<?php

namespace App\Services\Users;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;

class UserService
{
    protected AuditService $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    /**
     * Create a new user with secure password hashing and audit logging.
     *
     * @throws InvalidArgumentException
     */
    public function createUser(array $data, User $actor): User
    {
        return DB::transaction(function () use ($data, $actor) {
            $roleEnum = $data['role'] instanceof UserRole
                ? $data['role']
                : UserRole::from($data['role']);

            $user = User::create([
                'name' => trim($data['name']),
                'email' => strtolower(trim($data['email'])),
                'role' => $roleEnum,
                'active' => isset($data['active']) ? (bool) $data['active'] : true,
                'password' => Hash::make($data['password']),
            ]);

            $this->auditService->log(
                'user.created',
                $user,
                null,
                [
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role->value,
                    'active' => $user->active,
                ]
            );

            return $user;
        });
    }

    /**
     * Update user general profile information (name, email, role, active).
     *
     * @throws InvalidArgumentException
     */
    public function updateUser(User $targetUser, array $data, User $actor): User
    {
        return DB::transaction(function () use ($targetUser, $data, $actor) {
            $user = User::where('id', $targetUser->id)->lockForUpdate()->firstOrFail();

            $oldValues = [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->value,
                'active' => $user->active,
            ];

            // 1. Role update check
            if (isset($data['role'])) {
                $newRole = $data['role'] instanceof UserRole ? $data['role'] : UserRole::from($data['role']);
                if ($newRole !== $user->role) {
                    $this->changeRoleInternal($user, $newRole, $actor);
                }
            }

            // 2. Status update check
            if (isset($data['active'])) {
                $newActive = (bool) $data['active'];
                if ($newActive !== (bool) $user->active) {
                    $this->toggleStatusInternal($user, $actor);
                }
            }

            // 3. Name & Email update
            $user->name = trim($data['name']);
            $user->email = strtolower(trim($data['email']));
            $user->save();

            $newValues = [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->value,
                'active' => $user->active,
            ];

            if ($oldValues['name'] !== $newValues['name'] || $oldValues['email'] !== $newValues['email']) {
                $this->auditService->log(
                    'user.updated',
                    $user,
                    ['name' => $oldValues['name'], 'email' => $oldValues['email']],
                    ['name' => $newValues['name'], 'email' => $newValues['email']]
                );
            }

            return $user->fresh();
        });
    }

    /**
     * Explicit toggle of user status (active <-> inactive).
     *
     * @throws InvalidArgumentException
     */
    public function toggleStatus(User $targetUser, User $actor): User
    {
        return DB::transaction(function () use ($targetUser, $actor) {
            $user = User::where('id', $targetUser->id)->lockForUpdate()->firstOrFail();
            return $this->toggleStatusInternal($user, $actor);
        });
    }

    /**
     * Internal status toggle logic with protections.
     */
    protected function toggleStatusInternal(User $user, User $actor): User
    {
        if ((int) $user->id === (int) $actor->id && $user->active) {
            throw new InvalidArgumentException('Un administrador no puede desactivar su propia cuenta.');
        }

        if ($user->active && $user->isAdmin()) {
            $activeAdminCount = User::where('role', UserRole::Admin)
                ->where('active', true)
                ->lockForUpdate()
                ->count();

            if ($activeAdminCount <= 1) {
                throw new InvalidArgumentException('No se puede desactivar al último administrador activo del sistema.');
            }
        }

        $oldActive = $user->active;
        $user->active = ! $oldActive;
        $user->save();

        $this->auditService->log(
            'user.status_changed',
            $user,
            ['active' => $oldActive],
            ['active' => $user->active]
        );

        return $user;
    }

    /**
     * Change user role with historical incompatibility checks.
     *
     * @throws InvalidArgumentException
     */
    public function changeRole(User $targetUser, UserRole|string $newRole, User $actor): User
    {
        return DB::transaction(function () use ($targetUser, $newRole, $actor) {
            $user = User::where('id', $targetUser->id)->lockForUpdate()->firstOrFail();
            $roleEnum = $newRole instanceof UserRole ? $newRole : UserRole::from($newRole);
            return $this->changeRoleInternal($user, $roleEnum, $actor);
        });
    }

    /**
     * Internal role change logic with protections.
     */
    protected function changeRoleInternal(User $user, UserRole $newRole, User $actor): User
    {
        if ($user->role === $newRole) {
            return $user;
        }

        if ((int) $user->id === (int) $actor->id && $user->isAdmin() && $newRole !== UserRole::Admin) {
            throw new InvalidArgumentException('Un administrador no puede cambiar el rol de su propia cuenta.');
        }

        if ($user->isAdmin() && $newRole !== UserRole::Admin && $user->active) {
            $activeAdminCount = User::where('role', UserRole::Admin)
                ->where('active', true)
                ->lockForUpdate()
                ->count();

            if ($activeAdminCount <= 1) {
                throw new InvalidArgumentException('No se puede cambiar el rol del último administrador activo del sistema.');
            }
        }

        if ($user->isStudent() && $user->hasStudentHistory()) {
            throw new InvalidArgumentException('No se puede cambiar el rol de un estudiante con historial de matrículas o calificaciones asociadas.');
        }

        if ($user->isTeacher() && $user->hasTeacherHistory()) {
            throw new InvalidArgumentException('No se puede cambiar el rol de un docente con asignaciones, calificaciones o publicaciones asociadas.');
        }

        $oldRole = $user->role;
        $user->role = $newRole;
        $user->save();

        $this->auditService->log(
            'user.role_changed',
            $user,
            ['role' => $oldRole->value],
            ['role' => $newRole->value]
        );

        return $user;
    }

    /**
     * Administrative password reset for a target user.
     *
     * @throws InvalidArgumentException
     */
    public function resetPassword(User $targetUser, string $newPassword, User $actor): User
    {
        return DB::transaction(function () use ($targetUser, $newPassword, $actor) {
            $user = User::where('id', $targetUser->id)->lockForUpdate()->firstOrFail();

            $user->password = Hash::make($newPassword);
            $user->save();

            $this->auditService->log(
                'user.password_reset_by_admin',
                $user,
                null,
                [
                    'target_user_id' => $user->id,
                    'email' => $user->email,
                    'reset_by' => $actor->id,
                ]
            );

            return $user;
        });
    }
}
