<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserManagement\AuthController;
use App\Http\Controllers\API\UserManagement\RoleController;
use App\Http\Controllers\API\UserManagement\PermissionController;
use App\Http\Controllers\API\UserManagement\HasPermissionController;
use App\Http\Controllers\API\UserManagement\UserRoleController;
use App\Http\Controllers\API\Store\ItemsController;
use App\Http\Controllers\API\Store\CategoryController;
use App\Http\Controllers\API\Store\UnitController;
use App\Http\Controllers\API\Store\ItemTypeController;
use App\Http\Controllers\API\Store\CompanyController;
use App\Http\Controllers\API\Store\ItemStoreController;
use App\Http\Controllers\API\Store\StoreDepartmentController;
use App\Http\Controllers\API\Store\RequisitionController;
use App\Http\Controllers\API\Store\PurchaseOrderController;
use App\Http\Controllers\API\Store\PurchaseListController;
use App\Http\Controllers\API\Store\VendorController;
use App\Http\Controllers\API\UserManagement\MailController;
use App\Http\Controllers\API\UserManagement\ForgotPasswordController;
use App\Http\Controllers\API\UserManagement\ChangePasswordController;
use App\Http\Controllers\API\Store\IssueItemController;
use App\Http\Controllers\API\Store\ReturnedItemController;
use App\Http\Controllers\API\Store\ApprovedReturnController;
use App\Http\Controllers\API\Store\RepairItemController;
use App\Http\Controllers\API\Store\DiscardItemController;
use App\Models\UserManagement\Users;



// Route::middleware('web')->group(function () {
//     Route::post('/login', [AuthController::class, 'login']);
//     Route::post('/logout', [AuthController::class, 'logout']);
// });
Route::get('/sanctum/csrf-cookie', function () {
    return response()->json(['message' => 'CSRF cookie set']);
});

// Login using email and password

Route::post('/login', [AuthController::class, 'login']);
    // Route::post('/logout', [AuthController::class, 'logout']);


Route::post('/forgot-password', [AuthController::class, 'forgetPassword']);
Route::post('/add-category', [CategoryController::class, 'store']); //->middleware('permission:add_categories');
Route::put('/update/{id}', [CategoryController::class, 'update']); //->middleware('permission:update_categories');
Route::patch('/toggle/{id}', [CategoryController::class, 'toggle']); //->middleware('permission:toggle_categories');
// Route::put('/user/update/{id}', [AuthController::class, 'update']);
// Route::get('/show-user/{id}', [AuthController::class, 'show']);
// Route::get('/show-users', [AuthController::class, 'index']);
// Route::get('/view', [ItemsController::class, 'view']);
// Route::get('/view-role', [RoleController::class, 'getAllRole']);
// Route::post('/company/add', [CompanyController::class, 'store']);
Route::get('/category-view', [CategoryController::class, 'index']); //->middleware('permission:view_categories');
// Route::get('/company-view', [CompanyController::class, 'index']);
Route::post('/requisition/create', [RequisitionController::class, 'store']);
Route::get('/show/{requisition_no}', [RequisitionController::class, 'show']);
Route::post('/purchase/add', [PurchaseListController::class, 'store']);
Route::get('/purchase/view', [PurchaseListController::class, 'view']);
Route::get('/purchase/show/{purchase_no}', [PurchaseListController::class, 'view']);
Route::put('/purchase/update/{purchase_no}', [PurchaseListController::class, 'update']);









Route::middleware('auth:sanctum')->group(function (){

    // Authorization routes
    Route::prefix('auth')->group(function(){
        Route::post('/register', [AuthController::class, 'register']); //->middleware('permission:create_user');
        Route::put('/update/{uuid}', [AuthController::class, 'update'])->middleware('permission:update_user');
        Route::delete('/delete/{id}', [AuthController::class, 'destroy'])->middleware('permission:delete_user');
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
        Route::get('/show-user/{uuid}', [AuthController::class, 'show']);
        Route::get('/show-users', [AuthController::class, 'index']);
        Route::patch('/toggle/{id}', [AuthController::class, 'toggle']);
        Route::get('/me', function (Request $request) {
            $roleName = Users::select(
                "roles.role_name as rolename",
            )->leftJoin('roles','users.role_id','=','roles.id')
            ->where('users.id',$request->user()->id)
            ->first();
            return response()->json([
                "data"=>$request->user(),
                "roleName" => $roleName->rolename ?? null,
            ],200);
        });

    


        // Password management
        Route::post('/send-password', [MailController::class, 'sendRandomPasswordThroughMail'])
            ->middleware('permission:send_password');
        Route::post('/change-password', [AuthController::class, 'changePassword']);

        // Role management
        Route::get('/view-role', [RoleController::class, 'getAllRole']);
        Route::post('/role', [RoleController::class, 'createRole'])->middleware('permission:create_role');
        Route::post('/assign-role', [UserRoleController::class, 'assignRole'])->middleware('permission:assign_role');
        Route::get('/user-role/{user_id}', [UserRoleController::class, 'getUserRole'])->middleware('permission:view_user_role');

        // Permission management
        Route::post('/permission', [PermissionController::class, 'createPermission'])->middleware('permission:create_permission');
        Route::post('/assign-permission', [HasPermissionController::class, 'assignPermission'])->middleware('permission:assign_permission');
        Route::post('/revoke-permission', [HasPermissionController::class, 'revokePermission'])->middleware('permission:revoke_permission');
        Route::get('/role-permissions/{role_id}', [HasPermissionController::class, 'getRolePermissions'])->middleware('permission:view_role_permission');
    });


    // From here store routes starts
    Route::prefix('store')->group(function() {
        
        // items 
        Route::prefix('items')->group(function(){
            Route::get('/view', [ItemsController::class, 'view'])->middleware('permission:view_items');
            Route::get('/show/{uuid}', [ItemsController::class, 'show'])->middleware('permission:show_items');
            Route::post('/add', [ItemsController::class, 'store'])->middleware('permission:add_items');
            Route::delete('/delete/{id}', [ItemsController::class, 'destroy'])->middleware('permission:delete_items');
            Route::put('/update/{id}', [ItemsController::class, 'update'])->middleware('permission:update_items');
            Route::patch('/toggle/{id}', [ItemsController::class, 'toggleStatus'])->middleware('permission:toggle_items');
        });

        // categories
        Route::prefix('categories')->group(function () {
            Route::get('/category-view', [CategoryController::class, 'index']); //->middleware('permission:view_categories');

            Route::get('/active', [CategoryController::class, 'active'])->middleware('permission:active_categories');
            Route::get('/{id}', [CategoryController::class, 'show'])->middleware('permission:show_categories');
            Route::delete('/delete/{id}', [CategoryController::class, 'destroy'])->middleware('permission:delete_categories');
        });

        // Unit-subunit
        Route::prefix('units')->group(function(){
            Route::get('/view', [UnitController::class, 'view'])->middleware('permission:view_unit');
            Route::post('/add', [UnitController::class, 'store'])->middleware('permission:add_unit');
            Route::get('/show/{id}', [UnitController::class, 'show'])->middleware('permission:show_unit');
            Route::put('/update/{id}', [UnitController::class, 'update']); //->middleware('permission:update_unit');
            Route::delete('/delete/{id}', [UnitController::class, 'destroy'])->middleware('permission:delete_unit');
            Route::get('/active', [UnitController::class, 'active'])->middleware('permission:active_unit');
            Route::patch('/toggle/{id}', [UnitController::class, 'toggle'])->middleware('permission:toggle_unit');
        });

        // Item Type
        Route::prefix('item-type')->group(function () {
            Route::get('/view', [ItemTypeController::class, 'index'])->middleware('permission:view_item_type');
            Route::get('/active', [ItemTypeController::class, 'active'])->middleware('permission:active_item_types');
            Route::post('/add', [ItemTypeController::class, 'store'])->middleware('permission:add_item_type');
            Route::get('/show/{id}', [ItemTypeController::class, 'show'])->middleware('permission:show_item_type');
            Route::put('/update/{id}', [ItemTypeController::class, 'update'])->middleware('permission:update_item_type');
            Route::delete('/delete/{id}', [ItemTypeController::class, 'destroy'])->middleware('permission:delete_item_type');
            Route::patch('/toggle/{id}', [ItemTypeController::class, 'toggle'])->middleware('permission:toggle_item_type');
        });

        // company name
        Route::prefix('companies')->group(function () {
            Route::get('/view', [CompanyController::class, 'index'])->middleware('permission:view_companies');
            Route::get('/active', [CompanyController::class, 'active'])->middleware('permission:active_companies');
            Route::post('/add', [CompanyController::class, 'store'])->middleware('permission:add_companies');
            Route::get('/show/{id}', [CompanyController::class, 'show'])->middleware('permission:show_companies');
            Route::put('/update/{id}', [CompanyController::class, 'update']); //->middleware('permission:update_companies');
            Route::delete('/delete/{id}', [CompanyController::class, 'destroy'])->middleware('permission:delete_companies');
            Route::patch('/toggle/{id}', [CompanyController::class, 'toggle'])->middleware('permission:toggle_companies');
        });

        // store name
        Route::prefix('item-store')->group(function () {
            Route::get('/view', [ItemStoreController::class, 'index'])->middleware('permission:view_item_store');
            Route::get('/active', [ItemStoreController::class, 'active'])->middleware('permission:active_item_store');
            Route::post('/add', [ItemStoreController::class, 'store'])->middleware('permission:add_item_store');
            Route::get('/show/{id}', [ItemStoreController::class, 'show'])->middleware('permission:show_item_store');
            Route::put('/update/{id}', [ItemStoreController::class, 'update'])->middleware('permission:update_item_store');
            Route::delete('/delete/{id}', [ItemStoreController::class, 'destroy'])->middleware('permission:delete_item_store');
            Route::patch('/toggle/{id}', [ItemStoreController::class, 'toggle'])->middleware('permission:toggle_item_store');
        });

        // store department name

        Route::prefix('department')->group(function () {
            Route::get('/view', [StoreDepartmentController::class, 'view'])->middleware('permission:view_department');
            Route::post('/add', [StoreDepartmentController::class, 'store'])->middleware('permission:add_department');
            Route::get('/show/{id}', [StoreDepartmentController::class, 'show'])->middleware('permission:show_department');
            Route::put('/update/{id}', [StoreDepartmentController::class, 'update'])->middleware('permission:update_department');
            Route::delete('/delete/{id}', [StoreDepartmentController::class, 'destroy'])->middleware('permission:delete_department');
            Route::patch('/toggle/{id}', [StoreDepartmentController::class, 'toggleStatus'])->middleware('permission:toggle_department');
        });


        // requisition routes
        Route::prefix('requisition')->group(function () {
            Route::get('/view', [RequisitionController::class, 'index'])->middleware('permission:view_requisition');
            Route::post('/create', [RequisitionController::class, 'store']); //->middleware('permission:add_requisition');
            Route::get('/view/{uuid}', [RequisitionController::class, 'show']); //->middleware('permission:show_requisition');
            Route::put('/update/{uuid}', [RequisitionController::class, 'update']); //->middleware('permission:update_requisition');

            Route::put('/status/{requisition_no}', [RequisitionController::class, 'updateStatus']); //->middleware('permission:update_requisition');
            Route::delete('/delete/{requisition_no}', [RequisitionController::class, 'destroy'])->middleware('permission:delete_requisition');
        });


        // Purchase order routes
        Route::prefix('purchase-order')->group(function () {
            Route::get('/view', [PurchaseOrderController::class, 'view'])->middleware('permission:view_purchase_order'); 
            Route::post('/create', [PurchaseOrderController::class, 'store'])->middleware('permission:create_purchase_order');  
            Route::get('/show/{uuid}', [PurchaseOrderController::class, 'show'])->middleware('permission:show_purchase_order');    
            Route::put('/update/{uuid}', [PurchaseOrderController::class, 'update'])->middleware('permission:update_purchase_order');   
            Route::delete('/delete/{uuid}', [PurchaseOrderController::class, 'destroy'])->middleware('permission:delete_purchase_order');
        });

        // Purchase List and Purchase Details Routes
        Route::prefix('purchase')->group(function () {
            Route::get('/view', [PurchaseListController::class, 'view'])->middleware('permission:view_purchase');
            Route::post('/add', [PurchaseListController::class, 'store'])->middleware('permission:add_purchase');
            Route::put('/update/{purchase_no}', [PurchaseListController::class, 'update']);
            Route::get('/show/{purchase_no}', [PurchaseListController::class, 'show'])->middleware('permission:show_purchase');
            Route::delete('/delete/{purchase_no}', [PurchaseListController::class, 'destroy'])->middleware('permission:delete_purchase');
        });

        // Vendors route
        Route::prefix('vendor')->group(function(){
            Route::post('/add', [VendorController::class, 'create'])->middleware('permission:add_vendor');
            Route::get('/view', [VendorController::class, 'view'])->middleware('permission:view_vendor');
            Route::put('/update/{id}', [VendorController::class, 'update'])->middleware('permission:update_vendor');
            Route::delete('/delete/{id}', [VendorController::class, 'delete'])->middleware('permission:delete_vendor');
        });

        // issue items routes
        Route::prefix('issue')->group(function () {
            Route::get('/view', [IssueItemController::class, 'view'])->middleware('permission:view_issue');
            Route::post('/add', [IssueItemController::class, 'store'])->middleware('permission:add_issue');
            Route::get('/show/{issue_no}', [IssueItemController::class, 'show'])->middleware('permission:show_issue');
            Route::put('/update/{issue_no}', [IssueItemController::class, 'update'])->middleware('permission:update_issue');
            Route::delete('/delete/{issue_no}', [IssueItemController::class, 'destroy'])->middleware('permission:delete_issue');
        });

        // return items routes
        Route::prefix('return-item')->group(function () {
            Route::get('/view', [ReturnedItemController::class, 'index'])->middleware('permission:view_return_item');
            Route::post('/add', [ReturnedItemController::class, 'store'])->middleware('permission:add_return_item');
            Route::get('/show/{returned_id}', [ReturnedItemController::class, 'show'])->middleware('permission:show_return_item');
            Route::put('/update/{returned_id}', [ReturnedItemController::class, 'update'])->middleware('permission:update_return_item');
            Route::delete('/delete/{returned_id}', [ReturnedItemController::class, 'destroy'])->middleware('permission:delete_return_item');
        });

        // approved return routes
        Route::prefix('approved-return')->group(function () {
            Route::post('/approve/{returned_id}', [ApprovedReturnController::class, 'approve'])->middleware('permission:approve_return');
            Route::get('/view', [ApprovedReturnController::class, 'index'])->middleware('permission:view_approve_return');
            Route::get('/show/{approved_id}', [ApprovedReturnController::class, 'show'])->middleware('permission:show_approve_return');
            Route::delete('/delete/{approved_id}', [ApprovedReturnController::class, 'destroy'])->middleware('permission:delete_approve_return');
        });

        // repair items routes
        Route::prefix('repair-items')->group(function () {
            Route::post('/add', [RepairItemController::class, 'store'])->middleware('permission:add_repair');
            Route::get('/view', [RepairItemController::class, 'index'])->middleware('permission:view_repair');
            Route::get('/show/{return_id}', [RepairItemController::class, 'show'])->middleware('permission:show_repair');
            Route::put('/update/{return_id}', [RepairItemController::class, 'update'])->middleware('permission:update_repair');
            Route::delete('/delete/{return_id}', [RepairItemController::class, 'destroy'])->middleware('permission:delete_repair');
        });

        // discarded routes
        Route::prefix('discard')->group(function () {
            Route::get('/view', [DiscardItemController::class, 'index'])->middleware('permission:view_discard');
            Route::post('/add', [DiscardItemController::class, 'store'])->middleware('permission:add_discard');
            Route::get('/show/{id}', [DiscardItemController::class, 'show'])->middleware('permission:show_discard');
            Route::delete('/delete/{id}', [DiscardItemController::class, 'destroy'])->middleware('permission:delete_discard');
        });
    });


    
    // Route::put('/update-bill', [AuthController::class, 'updateBill'])->middleware('permission:update-bill');
});







    // Route::middleware(['auth:sanctum'])->group(function () {
    // Route::get('/patients', [PatientController::class, 'index'])
    //     ->middleware('permission:view patients');

    // Route::post('/patients', [PatientController::class, 'store'])
    //     ->middleware('permission:create patients');

    // Route::delete('/patients/{id}', [PatientController::class, 'destroy'])
    //     ->middleware('permission:delete patients');
// });
/*
   use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

// Create permissions
Permission::create(['name' => 'view users']);
Permission::create(['name' => 'delete users']);
Permission::create(['name' => 'create users']);

// Create role and assign permissions
$admin = Role::create(['name' => 'admin']);
$admin->givePermissionTo(['view users', 'delete users', 'create users']);

// Assign role to a user
$user = \App\Models\User::find(1);
$user->assignRole('admin');

// */

