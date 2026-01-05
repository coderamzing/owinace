<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Filament\Resources\Resource;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\ModelHasRole;
use App\Models\User;
use Illuminate\Support\Facades\DB;

trait HasPermission
{
    private static $permissions = [];

    public static function hasPermissionTo(string $permission): bool
    {
        //dd($permission);
    
        if (empty(self::$permissions)) {
            $user = Auth::user();
            if($user->roles->first()){
                self::$permissions = $user->roles->first()?->permissions->pluck('name')->toArray();
            }else{
                $hasRole = DB::table('model_has_roles')->where('model_id', $user->id)
                    ->where('model_type', User::class)
                    ->first();
                if($hasRole){
                    $role = Role::find($hasRole->role_id);
                    self::$permissions = $role->permissions->pluck('name')->toArray();
                }
            }
        }
        return in_array($permission, self::$permissions);
    }

    public static function canAccess(array $parameters = []): bool
    {
        $key = self::getPermissionKeyForResource(static::class, 'list');
        return self::hasPermissionTo($key);
    }

    public static function getPermissionKeyForResource(string $resourceClass, $permission = null): string
    {
        $mapping = [];
        if(!empty(self::$permission)){
            return self::$permission ?? '';
        }else if(is_subclass_of(static::class, Resource::class)) {
            $className = class_basename($resourceClass);
            $prefix = str_replace('Resource', '', $className);
            if(isset($mapping[$className])){
                return $mapping[$className] . '.' . $permission;
            }
            return strtolower($prefix) . '.' . $permission;
        }else if(property_exists(static::class, 'resource')){
            $resourceClass =  static::$resource ?? null;
            $className = class_basename($resourceClass);
            $prefix = str_replace('Resource', '', $className);
            if(isset($mapping[$className])){
                return $mapping[$className] . '.' . $permission;
            }
            return strtolower($prefix) . '.' . $permission;
        }
        return '';
    }
    
    public static function canViewAny(): bool
    {
        //return true;
        return self::hasPermissionTo(self::$permission ?? '');
    }
    


    /**
     * Check if user can edit records
     */
    public static function canEdit($record): bool
    {
        $key = self::getPermissionKeyForResource(static::class, 'edit');
        return self::hasPermissionTo($key);
    }

    /**
     * Check if user can create records
     */
    public static function canCreate(): bool
    {
        $key = self::getPermissionKeyForResource(static::class, 'create');
        return self::hasPermissionTo($key);
    }
    
    /**
     * Check if user can delete records
     */
    public static function canDelete($record): bool
    {
        $key = self::getPermissionKeyForResource(static::class, 'delete');
        return self::hasPermissionTo($key);
    }

    /**
     * Check if user can export records
     */
    public static function canExport(): bool
    {
        $key = self::getPermissionKeyForResource(static::class, 'export');
        return self::hasPermissionTo($key);
    }

    /**
     * Check if user can import records
     */
    public static function canImport(): bool
    {
        $key = self::getPermissionKeyForResource(static::class, 'import');
        return self::hasPermissionTo($key);
    }

    public static function canBulkAdd(): bool
    {
        $key = self::getPermissionKeyForResource(static::class, 'bulk_add');
        return self::hasPermissionTo($key);
    }

    //  public static function canEmail(): bool
    // {
    //     $key = self::getPermissionKeyForResource(static::class, '');
    //     return self::hasPermissionTo($key);
    // }

    //  public static function canApprove(): bool
    // {
    //     $key = self::getPermissionKeyForResource(static::class, '');
    //     return self::hasPermissionTo($key);
    // }

    //  public static function canReject(): bool
    // {
    //     $key = self::getPermissionKeyForResource(static::class, '');
    //     return self::hasPermissionTo($key);
    // }

    /**
     * Check if user can import records
     */
    public static function canAlert(): bool
    {
        $key = self::getPermissionKeyForResource(static::class, 'alert');
        return self::hasPermissionTo($key);
    }

    /**
     * Get permission-aware actions for a resource table
     */
    public static function getPermissionActionsFor(string $resourceClass): array
    {
        $actions = [];
        
        // Only add edit action if user has edit permission
        $editKey = self::getPermissionKeyForResource($resourceClass, 'edit');
        if (self::hasPermissionTo($editKey)) {
            $actions[] = \Filament\Actions\EditAction::make();
        }
        
        // Only add delete action if user has delete permission
        $deleteKey = self::getPermissionKeyForResource($resourceClass, 'delete');
        if (self::hasPermissionTo($deleteKey)) {
            $actions[] = \Filament\Actions\DeleteAction::make();
        }
        
        return $actions;
    }

    /**
     * Get permission-aware bulk actions for a resource table
     */
    public static function getPermissionBulkActionsFor(string $resourceClass): array
    {
        $actions = [];
        
        // Only add delete bulk action if user has delete permission
        $deleteKey = self::getPermissionKeyForResource($resourceClass, 'delete');
        if (self::hasPermissionTo($deleteKey)) {
            $actions[] = \Filament\Actions\DeleteBulkAction::make();
        }
        
        return $actions;
    }

}
