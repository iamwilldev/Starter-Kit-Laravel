<?php

namespace App\Http\Controllers\Admin\Auth;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Requests\Auth\role\editRequest;
use App\Http\Requests\Auth\role\roleRequest;
use App\Repositories\Auth\Role\RoleResponse;

class RoleController extends Controller
{
    protected $RoleResponse;
    public function __construct(RoleResponse $RoleResponse)
    {
        $this->RoleResponse = $RoleResponse;
    }

    public function index(Request $request)
    {
        if($request->ajax()) {

            $result = $this->RoleResponse->datatable();
                return DataTables::eloquent($result)


                ->addColumn('action', function ($action) {

                    if (auth()->user()->can('Role Trash')) {
                        $Trash  =  '
                                        <button type="button" class="btn btn-danger btn-sm btn-size"
                                            onclick="isTrash('.$action->id.')">
                                            Trash
                                        </button>
                                    ';
                    } else {
                        $Trash = '';
                    }

                    if (auth()->user()->can('Role View')) {
                        $View   =   '
                                        <a href="'.route('role.view',$action->uuid).'" type="button" 
                                            class="btn btn-success btn-sm btn-size">
                                            View
                                        </a>
                                    ';
                    } else {
                        $View   = '';
                    }

                    if (auth()->user()->can('Role Edit')) {
                        $Edit   =   '
                                        <a href="'.route('role.edit',$action->uuid).'" type="button"
                                            class="btn btn-primary btn-sm btn-size">
                                            Edit
                                        </a>
                                    ';            
                    } else {
                        $Edit   = '';
                    }
                        return $Trash." ".$View." ".$Edit;
                })

                ->addColumn('count', function ($count) {
                    return count($count->permissions). " Permissions";
                })

                ->editColumn('created_at', function ($created) {
                    return Carbon::create($created->created_at)->format('Y-m-d H:i:s');
                })

                ->editColumn('guard_name', function ($name) {
                    return ucwords($name->guard_name);
                })

                ->rawColumns(['created_at','action'])
                ->escapeColumns(['action'])
                ->smart(true)
                ->make();
        }

        return view('master.auth.role.index');
    }

    public function create()
    {
        $authorities = $this->RoleResponse->permission();
            return view('master.auth.role.create',compact('authorities'));
    }

    public function store(roleRequest $request)
    {

        DB::beginTransaction();
        try {
            $this->RoleResponse->store($request);
                $notification = ['message'     => 'Successfully created Role.',
                                'alert-type'  => 'success',
                                'gravity'     => 'bottom',
                                'position'    => 'right'];
                    return redirect()->route('role.index')->with($notification);
        } catch (\Exception $e) {
            
            DB::rollBack();
            $notification = ['message'     => 'Failed to created Role.',
                             'alert-type'  => 'danger',
                             'gravity'     => 'bottom',
                             'position'    => 'right'];
                return redirect()->route('role.index')->with($notification);

        } finally {
            DB::commit();
        }
    }

    public function edit($id)
    {
        $authorities = $this->RoleResponse->permission();
        $result      = $this->RoleResponse->view($id);
            return view('master.auth.role.edit', compact('authorities','result'));
    }

    public function update(editRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            
            $this->RoleResponse->update($request, $id);
            $notification = ['message'     => 'Successfully updated Role.',
                             'alert-type'  => 'success',
                             'gravity'     => 'bottom',
                             'position'    => 'right'];
                return redirect()->route('role.index')->with($notification);

        } catch (\Exception $e) {
            
            DB::rollBack();
            $notification = ['message'     => 'Failed to updated Role.',
                             'alert-type'  => 'danger',
                             'gravity'     => 'bottom',
                             'position'    => 'right'];
                return redirect()->route('role.index')->with($notification);

        } finally {
            DB::commit();
        }
    }

    public function view($id)
    {
        $authorities = $this->RoleResponse->permission();
        $result      = $this->RoleResponse->view($id);
            return view('master.auth.role.view', compact('authorities','result'));
    }

    public function trash($id)
    {
        DB::beginTransaction();
        try {
            $this->RoleResponse->transh($id);
            $success = true;
        } catch (\Exception $e) {
            DB::rollBack();
            $message = "Failed to moving data Trash";
            $success = false;
        } finally {
            DB::commit();
        }
            if($success == true) {
                /**
                 * Return response true
                 */
                return response()->json([
                    'success' => $success
                ]);
            } elseif ($success == false) {
                /**
                 * Return response false
                 */
                return response()->json([
                    'success' => $success,
                    'message' => $message,
                ]);
            }
    }

    public function dataTrash(Request $request)
    {
        if($request->ajax()) {

            $result = $this->RoleResponse->tableTrashed();
                return DataTables::eloquent($result)

                ->addColumn('action', function ($action) {

                    if (auth()->user()->can('Role Restore')) {
                        $Restore  = '
                                        <button type="button" class="btn btn-primary btn-sm btn-size"
                                            onclick="isRestore('.$action->id.')">
                                            Restore
                                        </button>
                                    ';
                    } else {
                        $Restore = '';
                    }

                    if (auth()->user()->can('Role Delete')) {
                        $Delete  =  '
                                        <button type="button" class="btn btn-danger btn-sm btn-size"
                                            onclick="isDelete('.$action->id.')">
                                            Delete
                                        </button>
                                    ';
                    } else {
                        $Delete  = '';
                    }

                    
                        return $Restore." ".$Delete;
                })


                ->addColumn('count', function ($count) {
                    return count($count->permissions). " Permissions";
                })

                ->editColumn('deleted_at', function ($deleted) {
                    return Carbon::create($deleted->deleted_at)->format('Y-m-d H:i:s');
                })

                ->rawColumns(['action'])
                ->escapeColumns(['action'])
                ->smart(true)
                ->make();
        }
            return view('master.auth.role.trash');
    }

    public function restore($id)
    {
        try {
            $this->RoleResponse->restore($id);
            $success = true;
        } catch (\Exception $e) {
            $message = "Failed to Restore data Role.";
            $success = false;
        }
            if($success == true) {
                /**
                 * Return response true
                 */
                return response()->json([
                    'success' => $success
                ]);
            } elseif ($success == false) {
                /**
                 * Return response false
                 */
                return response()->json([
                    'success' => $success,
                    'message' => $message,
                ]);
            }
    }
    
    /**
     * Delete Permanent Data Role
     */
    public function delete($id)
    {
        try {
            $this->RoleResponse->delete($id);
            $success = true;
        } catch (\Exception $e) {
            $message = "Failed to Delete data Role.";
            $success = false;
        }
            if($success == true) {
                /**
                 * Return response true
                 */
                return response()->json([
                    'success' => $success
                ]);
            } elseif ($success == false) {
                /**
                 * Return response false
                 */
                return response()->json([
                    'success' => $success,
                    'message' => $message,
                ]);
            }
    }
}
