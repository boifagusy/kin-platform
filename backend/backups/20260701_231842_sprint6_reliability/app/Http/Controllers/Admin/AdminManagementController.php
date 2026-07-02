<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Services\Admin\AuditService;
use App\Services\Admin\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminManagementController extends Controller
{
    protected PermissionService $permissionService;
    protected AuditService $auditService;

    public function __construct(PermissionService $permissionService, AuditService $auditService)
    {
        $this->permissionService = $permissionService;
        $this->auditService = $auditService;
    }

    /**
     * Check if user has permission to manage admins
     */
    private function checkPermission()
    {
        if (!$this->permissionService->hasPermission(PermissionService::PERM_MANAGE_ADMINS)) {
            abort(403, 'Insufficient permissions to manage admin accounts');
        }
    }

    /**
     * Check if trying to modify own account (for deactivation/role change)
     */
    private function checkSelfModification($adminId, $action = 'modify')
    {
        $currentAdminId = auth()->guard('admin')->id();
        
        if ($currentAdminId == $adminId) {
            if ($action === 'deactivate') {
                abort(403, 'You cannot deactivate your own admin account');
            }
            if ($action === 'role_change') {
                abort(403, 'You cannot change your own role');
            }
            // Allow editing own profile (name, email, password)
            return false;
        }
        return true;
    }

    /**
     * Check last super admin protection
     */
    private function checkLastSuperAdmin($admin, $action = 'deactivate')
    {
        if ($admin->role === 'super_admin') {
            $activeSuperAdmins = AdminUser::where('role', 'super_admin')->where('is_active', true)->count();
            if ($activeSuperAdmins <= 1) {
                if ($action === 'deactivate') {
                    abort(403, 'Cannot deactivate the last active super admin');
                }
                if ($action === 'role_change') {
                    abort(403, 'Cannot change the role of the last active super admin');
                }
            }
        }
    }

    public function index()
    {
        $admins = AdminUser::orderBy('created_at', 'desc')->paginate(20);
        return view('admin.admins.index', compact('admins'));
    }

    public function create()
    {
        $this->checkPermission();
        return view('admin.admins.create');
    }

    public function store(Request $request)
    {
        $this->checkPermission();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admin_users,email',
            'password' => 'required|min:8|confirmed',
            'role' => 'required|in:super_admin,support_admin,viewer_admin',
            'is_active' => 'sometimes|boolean',
        ]);

        $admin = AdminUser::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'is_active' => $request->input('is_active', true),
        ]);

        $this->auditService->logAdminCreated($admin, auth()->guard('admin')->user());

        return redirect()->route('admin.admins.index')->with('success', 'Admin created successfully');
    }

    public function show($id)
    {
        $admin = AdminUser::findOrFail($id);
        return view('admin.admins.show', compact('admin'));
    }

    public function edit($id)
    {
        $this->checkPermission();
        
        $admin = AdminUser::findOrFail($id);
        $isSelf = (auth()->guard('admin')->id() == $id);
        
        return view('admin.admins.edit', compact('admin', 'isSelf'));
    }

    public function update(Request $request, $id)
    {
        $this->checkPermission();

        $admin = AdminUser::findOrFail($id);
        $currentAdminId = auth()->guard('admin')->id();
        $isSelf = ($currentAdminId == $id);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', Rule::unique('admin_users', 'email')->ignore($id)],
            'password' => 'nullable|min:8|confirmed',
            'role' => 'sometimes|in:super_admin,support_admin,viewer_admin',
            'is_active' => 'sometimes|boolean',
        ]);

        $oldValues = $admin->only(['name', 'email', 'role', 'is_active']);
        
        $updateData = [];
        if ($request->has('name')) $updateData['name'] = $request->name;
        if ($request->has('email')) $updateData['email'] = $request->email;
        if ($request->filled('password')) $updateData['password'] = Hash::make($request->password);
        
        // Role change: only allowed if not self
        if ($request->has('role') && !$isSelf) {
            $this->checkLastSuperAdmin($admin, 'role_change');
            $updateData['role'] = $request->role;
        } elseif ($request->has('role') && $isSelf) {
            // Silently ignore role change for self
        }
        
        // Status change: only allowed if not self
        if ($request->has('is_active') && !$isSelf) {
            $this->checkLastSuperAdmin($admin, 'deactivate');
            $updateData['is_active'] = $request->is_active;
        } elseif ($request->has('is_active') && $isSelf) {
            // Silently ignore status change for self
        }
        
        $admin->update($updateData);
        
        $newValues = $admin->only(['name', 'email', 'role', 'is_active']);
        
        // Only log if changes were made
        if (!empty($updateData)) {
            $this->auditService->logAdminUpdated($admin, $oldValues, $newValues);
        }

        $message = $isSelf ? 'Your profile updated successfully' : 'Admin updated successfully';
        return redirect()->route('admin.admins.index')->with('success', $message);
    }

    public function activate($id)
    {
        $this->checkPermission();
        
        $admin = AdminUser::findOrFail($id);
        $currentAdminId = auth()->guard('admin')->id();
        
        if ($currentAdminId == $id) {
            abort(403, 'You cannot activate your own admin account');
        }
        
        $this->checkLastSuperAdmin($admin, 'deactivate');
        
        $admin->is_active = true;
        $admin->save();
        
        $this->auditService->logAdminActivated($admin);

        return redirect()->back()->with('success', 'Admin activated successfully');
    }

    public function deactivate($id)
    {
        $this->checkPermission();
        
        $admin = AdminUser::findOrFail($id);
        $currentAdminId = auth()->guard('admin')->id();
        
        if ($currentAdminId == $id) {
            abort(403, 'You cannot deactivate your own admin account');
        }
        
        $this->checkLastSuperAdmin($admin, 'deactivate');
        
        $admin->is_active = false;
        $admin->save();
        
        $this->auditService->logAdminDeactivated($admin);

        return redirect()->back()->with('success', 'Admin deactivated successfully');
    }
}
