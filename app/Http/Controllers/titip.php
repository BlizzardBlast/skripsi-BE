<?php

namespace App\Http\Controllers;

use DataTables;
use Carbon\Carbon;
use App\Models\StoreData;
use Illuminate\Http\Request;
use App\Models\FrmMonitoring;
use App\Models\FormMonitoring;
use App\Models\AccessDashboard;
use App\Models\FrmMonitoringAlert;
use Illuminate\Routing\Controller;
use App\Models\DashboardMonitoring;
use App\Models\DataSourceDashboard;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Auth;
use App\Models\FrmMonitoringAlertApi;
use App\Models\FrmMonitoringDashboard;
use App\Models\FrmMonitoringStoreData;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use App\Models\FrmMonitoringAlertGrafana;
use App\Models\FrmMonitoringAlertGrafanaCondition;
use App\Models\FrmMonitoringAlertLogstash;
use App\Models\FrmMonitoringDashboardAccess;
use Illuminate\Console\View\Components\Alert;
use App\Models\FrmMonitoringDashboardDataSource;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;

class FormMonitoringController extends Controller
{
    public function ajaxStoreDataGet(Request $request)
    {

        $findUser = User::find(Auth::user()->id);
        $findUserNip = $findUser->udomain;


        $currentUserLastFormStatus = FrmMonitoring::where('nip', $findUserNip)->latest()->pluck('status')->first();

        if ($currentUserLastFormStatus === 'Draft') {
            $formMonitoring = FrmMonitoring::where('nip', $findUserNip)->latest()->first();
        } else {
            $formMonitoring = null;
        }


        $currentId = 0;

        if (!is_null($formMonitoring)) {
            $currentId = $formMonitoring->id;
        }


        $discoveryData = null;


        //////



        $data = FrmMonitoringStoreData::where('frm_monitoring_id', $currentId)->get();;

        // Return the data as JSON
        return DataTables::of($data)->make();
    }

    public function ajaxStoreDataGetSpecific($id)
    {
        $editData = FrmMonitoringStoreData::findOrFail($id);


        return view('form_monitoring.formMonitoring', ['editData' => $editData]);
    }

    public function ajaxStoreDataDefaultValue($id)
    {
        $specific = FrmMonitoringStoreData::find($id);
        return response()->json($specific);
    }

    public function ajaxStoreDataPost(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'data_source' => 'required',
            'status' => 'required',
            'source_address' => ['nullable', 'regex:/^((\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])(\/([0-2]?[0-9]|3[0-2]))?$/'],
            'port' => ['required', 'numeric', 'min:0', 'max:65535'],
            'data_retention' => 'required|numeric|min:1',
            'index_rotation' => 'required',
            'time_field' => ['required', 'regex:/^[^\s]+$/'],
            'frm_monitoring_id' => 'required'
        ]);

        // Create the record
        FrmMonitoringStoreData::create($validatedData);

        // Return a response
        return response()->json(['message' => 'Successfully added new store data']);
    }

    public function ajaxStoreDataEdit(Request $request, $id)
    {
        $validatedData = $request->validate([
            'new_data_source' => 'required',
            'new_source_address' => ['nullable', 'regex:/^((\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])(\/([0-2]?[0-9]|3[0-2]))?$/'],
            'new_port' => ['required', 'numeric', 'min:0', 'max:65535'],
            'new_data_retention' => 'required|numeric|min:1',
            'new_index_rotation' => 'required',
            'new_time_field' => ['required', 'regex:/^[^\s]+$/'],
        ]);


        $data_source = $validatedData['new_data_source'];
        $source_address = $validatedData['new_source_address'];
        $port = $validatedData['new_port'];
        $data_retention = $validatedData['new_data_retention'];
        $index_rotation = $validatedData['new_index_rotation'];
        $time_field = $validatedData['new_time_field'];

        $updated = FrmMonitoringStoreData::findOrFail($id);
        $updated->data_source = $data_source;
        $updated->source_address = $source_address;
        $updated->port = $port;
        $updated->data_retention = $data_retention;
        $updated->index_rotation = $index_rotation;
        $updated->time_field = $time_field;
        $updated->save();


        return response()->json(['message' => 'Successfully editted new store data']);
    }

    public function ajaxStoreDataDelete($id)
    {

        $storeDataDelete = FrmMonitoringStoreData::findOrFail($id);

        $storeDataDelete->delete();

        return response()->json(['message' => 'Successfully deleted store data']);
    }



    public function ajaxVisualizationGet(Request $request)
    {
        $findUser = User::find(Auth::user()->id);
        $findUserNip = $findUser->udomain;

        $currentUserLastFormStatus = FrmMonitoring::where('nip', $findUserNip)->latest()->pluck('status')->first();
        $data = null;

        if ($currentUserLastFormStatus === 'Draft') {
            $formMonitoring = FrmMonitoring::where('nip', $findUserNip)->latest()->first();
            $currentId = $formMonitoring->id;
            $data = FrmMonitoringDashboard::where('frm_monitoring_id', $currentId)->get();
        } elseif ($currentUserLastFormStatus === 'Submitted') {
            $formMonitoring = FrmMonitoring::where('nip', $findUserNip)->latest()->first();
            $currentId = $formMonitoring->id;
            $data = FrmMonitoringDashboard::where('frm_monitoring_id', $currentId)->get();
        }

        // Return the data as JSON
        return DataTables::of($data ?? [])->make();
    }

    public function ajaxVisualizationPost(Request $request)
    {

        // Validate the request data
        $validatedData = $request->validate([
            'dashboard_name' => 'required',
            'dashboard_description' => 'required',
            'dashboard_pic' => 'required',
            'dashboard_mockup' => 'required|mimes:jpeg,png,jpg|max:3072',
            'frm_monitoring_id' => 'required'
        ]);

        $dashboardMockup = $request->file('dashboard_mockup');

        //////////////////////////////////////////////
        $findUser = User::find(Auth::user()->id);
        $findUserNip = $findUser->udomain;

        $currentUserLastFormStatus = FrmMonitoring::where('nip', $findUserNip)->latest()->pluck('status')->first();

        if ($currentUserLastFormStatus === 'Draft') {
            $prefix = FrmMonitoring::where('nip', $findUserNip)->latest()->pluck('project_name')->first();
        }

        // Generate a unique filename based on the created date
        $createdAt = Carbon::now()->format('YmdHis'); // Use Carbon to get the current timestamp
        $filename = $prefix . '-' . $createdAt . '.png';

        // Save the image file using storeAs()
        $dashboardMockup->storeAs('upload/images/formMonitoring/', $filename, 'public');

        $x = FrmMonitoringDashboard::create($validatedData);
        FrmMonitoringDashboard::where('id', $x->id)->update(['dashboard_mockup' => $filename]);



        // Return a response
        return response()->json(['message' => 'Successfully add new visualization']);
    }


    public function ajaxVisualizationEdit(Request $request, $id)
    {
        $validatedData = $request->validate([
            'new_dashboard_name' => 'required',
            'new_dashboard_description' => 'required',
            'new_dashboard_pic' => 'required',
        ]);


        $dashboard_name = $validatedData['new_dashboard_name'];
        $dashboard_description = $validatedData['new_dashboard_description'];
        $dashboard_pic = $validatedData['new_dashboard_pic'];

        $updated = FrmMonitoringDashboard::findOrFail($id);
        $updated->dashboard_name = $dashboard_name;
        $updated->dashboard_description = $dashboard_description;
        $updated->dashboard_pic = $dashboard_pic;
        if ($request->hasFile('new_dashboard_mockup')) {
            $newDashboardMockup = $request->file('new_dashboard_mockup');

            $request->validate([
                'new_dashboard_mockup' => 'required|mimes:jpeg,png,jpg|max:3072', // Add or remove file formats as needed
            ]);
            // Delete the previous dashboard mockup file
            Storage::disk('public')->delete('upload/images/formMonitoring/' . $updated->dashboard_mockup);

            $findUser = User::find(Auth::user()->id);
            $findUserNip = $findUser->udomain;

            $currentUserLastFormStatus = FrmMonitoring::where('nip', $findUserNip)->latest()->pluck('status')->first();

            if ($currentUserLastFormStatus === 'Draft') {
                $prefix = FrmMonitoring::where('nip', $findUserNip)->latest()->pluck('project_name')->first();
            }

            // Generate a unique filename based on the created date
            $createdAt = Carbon::now()->format('YmdHis'); // Use Carbon to get the current timestamp
            $filename = $prefix . '-' . $createdAt . '.png';

            // Save the new dashboard mockup file
            $newDashboardMockup->storeAs('upload/images/formMonitoring/', $filename, 'public');

            $updated->dashboard_mockup = $filename;
        }
        $updated->save();

        // Return a response
        return response()->json(['message' => 'Successfully edit visualization']);
    }

    public function ajaxVisualizationDelete($id)
    {

        $dataSourceDashboardDelete = FrmMonitoringDashboardDataSource::where('frm_monitoring_dashboard_id', $id)->delete();

        $dashboardMonitoringDelete = FrmMonitoringDashboard::findOrFail($id);
        // Get the filename of the picture
        $filename = $dashboardMonitoringDelete->dashboard_mockup;

        // Delete the picture file from storage
        if ($filename) {
            Storage::disk('public')->delete('upload/images/formMonitoring/' . $filename);
        }

        $dashboardMonitoringDelete->delete();

        return response()->json(['message' => 'Successfully delete visualization']);
    }

    public function ajaxVisualizationDefaultValue($id)
    {
        $specific = FrmMonitoringDashboard::find($id);
        return response()->json($specific);
    }


    public function ajaxDataSourceDashboardGet($id)
    {
        $finale = FrmMonitoringDashboardDataSource::with(['storeData' => function ($query) {
            $query->select('id', 'index_name'); // Load only necessary fields
        }])->where('frm_monitoring_dashboard_id', $id)->get();

        return DataTables::of($finale)
            ->addColumn('index_name', function ($data) {
                return $data->storeData ? $data->storeData->index_name : '-';
            })
            ->make();
    }

    public function ajaxDataSourceDashboardPost(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'data_source_type' => 'required',
            'device_name_solarwinds' => 'nullable',
            'frm_monitoring_store_data_id' => 'nullable',
            'frm_monitoring_dashboard_id' => 'required'
        ]);


        // Create the record
        FrmMonitoringDashboardDataSource::create($validatedData);

        // Return a response
        return response()->json(['message' => 'Successfully added Data Source Dashboard']);
    }


    public function ajaxDataSourceDashboardEdit(Request $request, $id)
    {
        $validatedData = $request->validate([
            'new_data_source_type' => 'nullable',
            'new_store_data_id' => 'nullable',
            'new_device_name_solarwinds' => 'nullable'
        ]);

        $data_source_type = $validatedData['new_data_source_type'];
        $device_name_solarwinds = $validatedData['new_device_name_solarwinds'];

        // Check if the data source type is 'Elastics' and set the device name to null
        $store_data_id = ($data_source_type === 'Solarwinds') ? null : $validatedData['new_store_data_id'];

        $updated = FrmMonitoringDashboardDataSource::findOrFail($id);
        $updated->data_source_type = $data_source_type;
        $updated->device_name_solarwinds = $device_name_solarwinds;
        $updated->frm_monitoring_store_data_id = $store_data_id;
        $updated->save();

        // Return a response
        return response()->json(['message' => 'Successfully editted Data Source Dashboard']);
    }

    public function ajaxDataSourceDashboardDelete($id)
    {

        $dataSourceDashboardDelete = FrmMonitoringDashboardDataSource::findOrFail($id);

        $dataSourceDashboardDelete->delete();

        return response()->json(['message' => 'Successfully deleted Data Source Dashboard']);
    }

    public function ajaxDataSourceDashboardDefaultValue($id)
    {
        $specific = FrmMonitoringDashboardDataSource::with(['storeData' => function ($query) {
            $query->select('id', 'index_name'); // Load only necessary fields
        }])->find($id);

        $response = [
            'data_source_type' => $specific->data_source_type,
            'store_data_id' => $specific->storeData ? $specific->storeData->id : null,
            'store_data_name' => $specific->storeData ? $specific->storeData->index_name : null,
            'device_name_solarwinds' => $specific->device_name_solarwinds,

        ];

        return response()->json($response);
    }

    public function ajaxAccessDashboardGet(Request $request)
    {
        $findUser = User::find(Auth::user()->id);
        $findUserNip = $findUser->udomain;


        $currentUserLastFormStatus = FrmMonitoring::where('nip', $findUserNip)->latest()->pluck('status')->first();

        if ($currentUserLastFormStatus === 'Draft') {
            $formMonitoring = FrmMonitoring::where('nip', $findUserNip)->latest()->first();
        } else {
            $formMonitoring = null;
        }




        $currentId = 0;

        if (!is_null($formMonitoring)) {
            $currentId = $formMonitoring->id;
        }
        $accessDashboard = null;
        $finale = FrmMonitoringDashboardAccess::with(['dashboardMonitoring' => function ($query) {
            $query->select('id', 'dashboard_name'); // Load only necessary fields
        }])->where('frm_monitoring_id', $currentId)->get();

        return DataTables::of($finale)
            ->addColumn('dashboard_name', function ($data) {
                return $data->dashboardMonitoring ? $data->dashboardMonitoring->dashboard_name : '-';
            })
            ->make();

        // Return the data as JSON
        return DataTables::of($data)->make();
    }

    public function ajaxAccessDashboardPost(Request $request)
    {

        if($request->duration == "Temporary"){
            $validatedData = $request->validate([
                'frm_monitoring_dashboard_id' => 'required',
                'role' => 'required',
                'duration' => 'required',
                'duration_date' => 'required',
                'udomain' => 'required',
                'frm_monitoring_id' => 'required'
            ]);
        }
        else if($request->duration == "Permanent"){
            $validatedData = $request->validate([
                'frm_monitoring_dashboard_id' => 'required',
                'role' => 'required',
                'duration' => 'required',
                'duration_date' => 'nullable',
                'udomain' => 'required',
                'frm_monitoring_id' => 'required'
            ]);
        }
        // Create the record
        FrmMonitoringDashboardAccess::create($validatedData);
        // Return a response
        return response()->json(['message' => 'Successfully added access dashboard']);
    }

    public function ajaxAccessDashboardDelete($id)
    {

        $accessDashboardDelete = FrmMonitoringDashboardAccess::findOrFail($id);

        $accessDashboardDelete->delete();

        return response()->json(['message' => 'Successfully deleted access dashboard']);
    }

    public function ajaxAccessDashboardEdit(Request $request, $id)
    {
        if($request->new_duration == "Temporary"){
            $validatedData = $request->validate([
                'new_frm_monitoring_dashboard_id' => 'required',
                'new_role' => 'required',
                'new_duration' => 'required',
                'new_duration_date' => 'required',
                'new_udomain' => 'required'
            ]);
        }
        else if($request->new_duration == "Permanent"){
            $validatedData = $request->validate([
                'new_frm_monitoring_dashboard_id' => 'required',
                'new_role' => 'required',
                'new_duration' => 'required',
                'new_duration_date' => 'nullable',
                'new_udomain' => 'required'
            ]);
        }


        $frm_monitoring_dashboard_id = $validatedData['new_frm_monitoring_dashboard_id'];
        $role = $validatedData['new_role'];
        $duration = $validatedData['new_duration'];
        $duration_date = $validatedData['new_duration_date'];
        $udomain = $validatedData['new_udomain'];


        $updated = FrmMonitoringDashboardAccess::findOrFail($id);
        $updated->frm_monitoring_Dashboard_id = $frm_monitoring_dashboard_id;
        $updated->role = $role;
        $updated->duration = $duration;
        $updated->duration_date = $duration_date;
        $updated->udomain = $udomain;
        $updated->save();

        return response()->json(['message' => 'Successfully edit access dashboard']);
    }

    public function ajaxAccessDashboardDefaultValue($id)
    {
        $specific = FrmMonitoringDashboardAccess::find($id);
        return response()->json($specific);
    }

    public function ajaxAlertGet(Request $request)
    {

        $findUser = User::find(Auth::user()->id);
        $findUserNip = $findUser->udomain;


        $currentUserLastFormStatus = FrmMonitoring::where('nip', $findUserNip)->latest()->pluck('status')->first();

        if ($currentUserLastFormStatus === 'Draft') {
            $formMonitoring = FrmMonitoring::where('nip', $findUserNip)->latest()->first();
        } else {
            $formMonitoring = null;
        }


        $currentId = 0;

        if (!is_null($formMonitoring)) {
            $currentId = $formMonitoring->id;
        }



        //////



        $data = FrmMonitoringAlert::where('frm_monitoring_id', $currentId)->get();

        // Return the data as JSON
        return DataTables::of($data)->make();
    }

    public function ajaxAlertDefaultValue($id)
    {
        $specific = FrmMonitoringAlert::find($id);
        return response()->json($specific);
    }

    public function ajaxAlertGrafanaDefaultValue($id)
    {
        $specific = FrmMonitoringAlertGrafana::where('frm_monitoring_alert_id', $id)->first();
        return response()->json($specific);
    }

    public function ajaxAlertGrafanaConditionDefaultValue($id)
    {
        $specific = FrmMonitoringAlertGrafanaCondition::find($id);
        return response()->json($specific);
    }

    public function ajaxAlertLogstashDefaultValue($id)
    {
        // $specific = FrmMonitoringAlertApi::where('frm_monitoring_alert_id',$id)->first();
        // return response()->json($specific);

        $specific = FrmMonitoringAlertLogstash::where('frm_monitoring_alert_id', $id)->with(['storeData' => function ($query) {
            $query->select('id', 'index_name'); // Load only necessary fields
        }])->first();

        $response = [
            'severity' => $specific->severity,
            'send_to_email' => $specific->send_to_email,
            'send_to_telegram_channel' => $specific->send_to_telegram_channel,
            'token_telegram' => $specific->token_telegram,
            'condition' => $specific->condition,
            'description' => $specific->description,
            'store_data_logstash_id' => $specific->storeData ? $specific->storeData->id : null,
            'store_data_logstash_name' => $specific->storeData ? $specific->storeData->index_name : null,

        ];

        return response()->json($response);
    }

    public function ajaxAlertApiDefaultValue($id)
    {
        // $specific = FrmMonitoringAlertApi::where('frm_monitoring_alert_id',$id)->first();
        // return response()->json($specific);

        $specific = FrmMonitoringAlertApi::where('frm_monitoring_alert_id', $id)->with(['storeData' => function ($query) {
            $query->select('id', 'index_name'); // Load only necessary fields
        }])->first();

        $response = [
            'severity' => $specific->severity,
            'send_to_email' => $specific->send_to_email,
            'send_to_telegram_channel' => $specific->send_to_telegram_channel,
            'token_telegram' => $specific->token_telegram,
            'condition' => $specific->condition,
            'description' => $specific->description,
            'store_data_api_id' => $specific->storeData ? $specific->storeData->id : null,
            'store_data_api_name' => $specific->storeData ? $specific->storeData->index_name : null,

        ];

        return response()->json($response);
    }



    public function ajaxAlertPost(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'alert_name' => 'required',
            'alert_type' => 'required',
            'frm_monitoring_id' => 'required'
        ]);

        // Create the record
        $newAlert = FrmMonitoringAlert::create($validatedData);

        // Return a response
        return response()->json([
            'message' => 'Successfully added new Alert',
            'alert_id' => $newAlert->id
        ]);
    }

    public function ajaxAlertGrafanaPost(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'graphic_name' => 'required',
            'severity' => 'required',
            'send_to_email' => 'required|email',
            'send_to_telegram_channel' => 'required',
            'evaluate_every' => 'required|numeric|min:1',
            'for_duration' => 'required|numeric|min:1',
            'no_data_handling' => 'required',
            'error_handling' => 'required',
            'frm_monitoring_dashboard_id' => 'required',
            'frm_monitoring_alert_id' => 'required'
        ]);

        // Create the record
        $grafanaAlert = FrmMonitoringAlertGrafana::create($validatedData);

        // Return a response
        return response()->json([
            'message' => 'Successfully added new Alert Grafana',
            'alert_id' => $grafanaAlert->id
        ]);
    }

    public function ajaxAlertApiPost(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'severity' => 'required',
            'send_to_email' => 'required|email',
            'send_to_telegram_channel' => 'required',
            'token_telegram' => 'required',
            'condition' => 'required',
            'description' => 'required',
            'frm_monitoring_store_data_id' => 'nullable',
            'frm_monitoring_alert_id' => 'required'
        ]);

        // Create the record
        $apiAlert = FrmMonitoringAlertApi::create($validatedData);

        // Return a response
        return response()->json([
            'message' => 'Successfully added new Alert Grafana Api',
            'alert_id' => $apiAlert->id
        ]);
    }


    public function ajaxAlertLogstashPost(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'severity' => 'required',
            'send_to_email' => 'required|email',
            'send_to_telegram_channel' => 'required',
            'token_telegram' => 'required',
            'condition' => 'required',
            'description' => 'required',
            'frm_monitoring_store_data_id' => 'nullable',
            'frm_monitoring_alert_id' => 'required'
        ]);

        $validatedData['frm_monitoring_store_data_id'] = $request->input('frm_monitoring_store_data_id_logstash');

        // Create the record
        $logstashAlert = FrmMonitoringAlertLogstash::create($validatedData);

        // Return a response
        return response()->json([
            'message' => 'Successfully added new Alert Grafana Logstash',
            'alert_id' => $logstashAlert->id, 'massage' => $request->input('frm_monitoring_store_data_id_logstash')
        ]);
    }

    public function ajaxAlertDelete($id)
    {
        $specific = FrmMonitoringAlert::find($id);

        $alertType = $specific->alert_type;

        if ($alertType === 'Grafana') {

            $idTemp = FrmMonitoringAlertGrafana::where('frm_monitoring_alert_id', $id)->pluck('id')->first();

            $grafanaConditionDelete = FrmMonitoringAlertGrafanaCondition::where('frm_monitoring_alert_grafana_id', $idTemp)->delete();

            $alertGrafanaDataDelete = FrmMonitoringAlertGrafana::where('frm_monitoring_alert_id', $id)->delete();
        } else if ($alertType === 'Logstash') {
            $alertLogstashDataDelete = FrmMonitoringAlertLogstash::where('frm_monitoring_alert_id', $id)->delete();
        } else if ($alertType === 'API') {
            $alertTApiDataDelete = FrmMonitoringAlertApi::where('frm_monitoring_alert_id', $id)->delete();
        }


        $alertDataDelete = FrmMonitoringAlert::findOrFail($id)->delete();



        return response()->json(['message' => 'Successfully deleted Alert Data and their sub data']);
    }


    public function ajaxAlertGrafanaConditionGet($id)
    {
        $data = FrmMonitoringAlertGrafanaCondition::where('frm_monitoring_alert_grafana_id', $id)->get();

        return DataTables::of($data)->make();
        // ->addColumn('index_name', function ($data) {
        //     return $data->storeData ? $data->storeData->index_name : '-';
        // })
        // ->make();
    }

    public function ajaxAlertGrafanaConditionDelete($id)
    {

        $alertGrafanaConditionDelete = FrmMonitoringAlertGrafanaCondition::findOrFail($id);
        $alertGrafanaId = $alertGrafanaConditionDelete->frm_monitoring_alert_grafana_id;

        $alertGrafanaConditionDelete->delete();

        // Check and update operator for the lowest ID condition
        $lowestIdCondition = FrmMonitoringAlertGrafanaCondition::where('frm_monitoring_alert_grafana_id', $alertGrafanaId)
            ->orderBy('id')
            ->first();

        if ($lowestIdCondition && ($lowestIdCondition->operator === 'AND' || $lowestIdCondition->operator === 'OR')) {
            $lowestIdCondition->update(['operator' => 'WHEN']);
        }

        return response()->json(['message' => 'Successfully deleted alert condition']);
    }

    public function ajaxAlertGrafanaConditionPost(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'operator' => 'required',
            'function' => 'required',
            'metric_name' => 'required',
            'condition' => 'required',
            'threshold' => 'required|numeric|min:1',
            'description' => 'required',
            'frm_monitoring_alert_grafana_id' => 'required'
        ]);



        $checker = FrmMonitoringAlertGrafanaCondition::where('frm_monitoring_alert_grafana_id', $validatedData['frm_monitoring_alert_grafana_id'])->get();

        if ($checker->isEmpty()) {
            if ($validatedData['operator'] === "AND" || $validatedData['operator'] === "OR") {
                $validatedData['operator'] = "WHEN";
            }
        } else {
            if ($validatedData['operator'] === "WHEN") {
                $validatedData['operator'] = "AND";
            }
        }

        // if ($validatedData['operator'] === "AND" || $validatedData['operator'] === "OR") {
        //     $validatedData['operator'] = "WHEN";
        // }




        // Create the record
        FrmMonitoringAlertGrafanaCondition::create($validatedData);

        // Return a response
        return response()->json(['message' => 'Successfully added Alert Grafana Condition']);
    }

    public function ajaxAlertEdit(Request $request, $id)
    {
        $validatedData = $request->validate([
            'edit_alert_name' => 'required',
            'edit_alert_type' => 'required',
        ]);


        $edit_alert_name = $validatedData['edit_alert_name'];
        $edit_alert_type = $validatedData['edit_alert_type'];


        $updated = FrmMonitoringAlert::findOrFail($id);
        $updated->alert_name = $edit_alert_name;
        $updated->alert_type = $edit_alert_type;
        $updated->save();


        return response()->json(['message' => 'Successfully editted alert']);
    }

    public function ajaxAlertGrafanaEdit(Request $request, $id)
    {
        $validatedData = $request->validate([
            'edit_severity' => 'required',
            'edit_graphic_name' => 'required',
            'edit_send_to_email' => 'required',
            'edit_send_to_telegram_channel' => 'required',
            'edit_evaluate_every' => 'required',
            'edit_for_duration' => 'required',
            'edit_no_data_handling' => 'required',
            'edit_error_handling' => 'required',
            'edit_frm_monitoring_dashboard_id' => 'required',
        ]);


        $edit_severity = $validatedData['edit_severity'];
        $edit_graphic_name = $validatedData['edit_graphic_name'];
        $edit_send_to_email = $validatedData['edit_send_to_email'];
        $edit_send_to_telegram_channel = $validatedData['edit_send_to_telegram_channel'];
        $edit_evaluate_every = $validatedData['edit_evaluate_every'];
        $edit_for_duration = $validatedData['edit_for_duration'];
        $edit_no_data_handling = $validatedData['edit_no_data_handling'];
        $edit_error_handling = $validatedData['edit_error_handling'];
        $edit_frm_monitoring_dashboard_id = $validatedData['edit_frm_monitoring_dashboard_id'];

        $updated = FrmMonitoringAlertGrafana::where('frm_monitoring_alert_id', $id)->first();
        $updated->severity = $edit_severity;
        $updated->graphic_name = $edit_graphic_name;
        $updated->send_to_email = $edit_send_to_email;
        $updated->send_to_telegram_channel = $edit_send_to_telegram_channel;
        $updated->evaluate_every = $edit_evaluate_every;
        $updated->for_duration = $edit_for_duration;
        $updated->no_data_handling = $edit_no_data_handling;
        $updated->error_handling = $edit_error_handling;
        $updated->frm_monitoring_dashboard_id = $edit_frm_monitoring_dashboard_id;





        $updated->save();


        return response()->json(['message' => 'Successfully editted alert Grafana']);
    }

    public function ajaxAlertLogstashEdit(Request $request, $id)
    {
        $validatedData = $request->validate([
            'edit_severity_logstash' => 'required',
            'edit_send_to_email_logstash' => 'required',
            'edit_send_to_telegram_channel_logstash' => 'required',
            'edit_token_telegram_logstash' => 'required',
            'edit_condition_logstash' => 'required',
            'edit_description_logstash' => 'required',
            'edit_frm_monitoring_store_data_id_logstash' => 'required',
        ]);



        $edit_severity = $validatedData['edit_severity_logstash'];
        $edit_send_to_email = $validatedData['edit_send_to_email_logstash'];
        $edit_send_to_telegram_channel = $validatedData['edit_send_to_telegram_channel_logstash'];
        $edit_token_telegram = $validatedData['edit_token_telegram_logstash'];
        $edit_condition = $validatedData['edit_condition_logstash'];
        $edit_description = $validatedData['edit_description_logstash'];
        $edit_frm_monitoring_store_data_id = $validatedData['edit_frm_monitoring_store_data_id_logstash'];

        $updated = FrmMonitoringAlertLogstash::where('frm_monitoring_alert_id', $id)->first();
        $updated->severity = $edit_severity;
        $updated->send_to_email = $edit_send_to_email;
        $updated->send_to_telegram_channel = $edit_send_to_telegram_channel;
        $updated->token_telegram = $edit_token_telegram;
        $updated->condition = $edit_condition;
        $updated->description = $edit_description;
        $updated->frm_monitoring_store_data_id = $edit_frm_monitoring_store_data_id;

        $updated->save();


        return response()->json(['message' => 'Successfully editted alert Logstash']);
    }

    public function ajaxAlertApiEdit(Request $request, $id)
    {
        $validatedData = $request->validate([
            'edit_severity_api' => 'required',
            'edit_send_to_email_api' => 'required|email',
            'edit_send_to_telegram_channel_api' => 'required',
            'edit_token_telegram_api' => 'required',
            'edit_condition_api' => 'required',
            'edit_description_api' => 'required',
            'edit_frm_monitoring_store_data_id_api' => 'required',
        ]);


        $edit_severity = $validatedData['edit_severity_api'];
        $edit_send_to_email = $validatedData['edit_send_to_email_api'];
        $edit_send_to_telegram_channel = $validatedData['edit_send_to_telegram_channel_api'];
        $edit_token_telegram = $validatedData['edit_token_telegram_api'];
        $edit_condition = $validatedData['edit_condition_api'];
        $edit_description = $validatedData['edit_description_api'];
        $edit_frm_monitoring_store_data_id = $validatedData['edit_frm_monitoring_store_data_id_api'];

        $updated = FrmMonitoringAlertApi::where('frm_monitoring_alert_id', $id)->first();
        $updated->severity = $edit_severity;
        $updated->send_to_email = $edit_send_to_email;
        $updated->send_to_telegram_channel = $edit_send_to_telegram_channel;
        $updated->token_telegram = $edit_token_telegram;
        $updated->condition = $edit_condition;
        $updated->description = $edit_description;
        $updated->frm_monitoring_store_data_id = $edit_frm_monitoring_store_data_id;

        $updated->save();


        return response()->json(['message' => 'Successfully editted alert API']);
    }

    public function ajaxAlertGrafanaConditionEdit(Request $request, $id)
    {
        $validatedData = $request->validate([
            'edit_operator' => 'required',
            'edit_function' => 'required',
            'edit_metric_name' => 'required',
            'edit_condition' => 'required',
            'edit_threshold' => 'required|numeric|min:1',
            'edit_description' => 'required',
        ]);

        $checker = FrmMonitoringAlertGrafanaCondition::where('frm_monitoring_alert_grafana_id', $id)->first();
        $checker2 = FrmMonitoringAlertGrafanaCondition::where('frm_monitoring_alert_grafana_id', $id)->pluck('operator')->first();

        if ((is_null($checker) && is_null($checker2)) || $checker2 !== "WHEN") {
            $validatedData['edit_operator'] = ($validatedData['edit_operator'] === "AND" || $validatedData['edit_operator'] === "OR") ? "WHEN" : $validatedData['edit_operator'];
        } else {
            $validatedData['edit_operator'] = ($validatedData['edit_operator'] === "WHEN") ? "AND" : $validatedData['edit_operator'];
        }

        // Extract values from validated data
        $operator = $validatedData['edit_operator'];
        $function = $validatedData['edit_function'];
        $metric_name = $validatedData['edit_metric_name'];
        $condition = $validatedData['edit_condition'];
        $threshold = $validatedData['edit_threshold'];
        $description = $validatedData['edit_description'];

        // Update the record in the database
        $updated = FrmMonitoringAlertGrafanaCondition::findOrFail($id);
        $updated->operator = $operator;
        $updated->function = $function;
        $updated->metric_name = $metric_name;
        $updated->condition = $condition;
        $updated->threshold = $threshold;
        $updated->description = $description;
        $updated->save();

        return response()->json(['message' => 'Successfully edited Grafana Condition']);
    }











    //
    public function form()
    {

        $findUser = User::find(Auth::user()->id);
        $findUserNip = $findUser->udomain;

        $currentUserLastFormStatus = FrmMonitoring::where('nip', $findUserNip)->latest()->pluck('status')->first();
        $temp = 1;

        $currentDraftStoreData = null;
        if ($currentUserLastFormStatus === 'Draft') {
            $formMonitoring = FrmMonitoring::where('nip', $findUserNip)->latest()->first();
        } else {
            $formMonitoring = null;
            $temp = null;
        }


        $discoveryData = null;
        $currentDraftId = null;
        if (!is_null($formMonitoring)) {
            $currentId = $formMonitoring->latest()->pluck('id')->first();
            $currentDraftId = $formMonitoring->id;
            $discoveryData = FrmMonitoringStoreData::where('frm_monitoring_id', $currentId)->get();
        }

        $formMonitoringSelection = FrmMonitoring::where('status', 'Completed')->pluck('id');

        $storeData = FrmMonitoringStoreData::whereIn('frm_monitoring_id', $formMonitoringSelection)->get();

        //get current draft data
        $currentDraftStoreData = FrmMonitoringStoreData::where('frm_monitoring_id', $currentDraftId)->get();
        $currentDraftVisualization = FrmMonitoringDashboard::where('frm_monitoring_id', $currentDraftId)->get();
        $currentDraftAccessDashboard = FrmMonitoringDashboardAccess::where('frm_monitoring_id', $currentDraftId)->get();
        $currentDraftAlert = FrmMonitoringAlert::where('frm_monitoring_id', $currentDraftId)->get();


        $accessDashboard = FrmMonitoringDashboardAccess::all();

        $showAllUser = User::all();
        $activeClasses = ['serviceOffering_active'];
        $allVisualization = FrmMonitoringDashboard::whereHas('formMonitoring', function ($query) {
            $query->where('status', 'Completed');
        })->get();

        $currentDraftAlertId = FrmMonitoringAlert::where('frm_monitoring_id', $currentDraftId)->latest()->pluck('id')->first();

        $currentDraftAlertId = $currentDraftAlertId + 1;





        return view('form_monitoring.formMonitoring', compact('activeClasses', 'findUser', 'discoveryData', 'showAllUser', 'accessDashboard', 'formMonitoring', 'storeData', 'temp', 'currentDraftStoreData', 'currentDraftVisualization', 'currentDraftAccessDashboard', 'currentDraftAlert', 'allVisualization', 'currentDraftAlertId'));
    }

    public function postformDiscoveryData(Request $request)
    {
        $findUser = User::find(Auth::user()->id);
        $findUserNip = $findUser->udomain;
        $currentUserLastFormStatus = FrmMonitoring::where('nip', $findUserNip)->latest()->pluck('status')->first();
        $formData = FrmMonitoring::all();
        $formDataContent = $formData->isEmpty();

        // SUBMITTED OR NULL
        if ($formData->isEmpty()) {

            $validatedData = $request->validate([
                'requester' => 'required',
                'nip' => 'required',
                'biro' => 'required',
                'request_type' => 'required',
                'monitoring_service' => 'nullable',
                'request_severity' => 'required',
                'project_name' => 'required',
                'status' => 'required'
            ]);




            FrmMonitoring::create($validatedData);
        } else if ($currentUserLastFormStatus === 'Submitted' || $currentUserLastFormStatus === 'Cancelled' || $currentUserLastFormStatus === 'Completed' || $currentUserLastFormStatus === 'On progress' || is_null($currentUserLastFormStatus)) {

            $validatedData = $request->validate([
                'requester' => 'required',
                'nip' => 'required',
                'biro' => 'required',
                'request_type' => 'required',
                'monitoring_service' => 'nullable',
                'request_severity' => 'required',
                'project_name' => 'required|min: 3',
                'status' => 'required'
            ]);



            FrmMonitoring::create($validatedData);
        }
        // UPDATE
        else if ($currentUserLastFormStatus === "Draft" && !is_null($formDataContent)) {

            $newValidatedData = $request->validate([

                'requester' => 'required',
                'nip' => 'required',
                'biro' => 'required',
                'request_type' => 'required',
                'monitoring_service' => 'nullable',
                'request_severity' => 'required',
                'project_name' => 'required|min: 3',
                'status' => 'required'
            ]);


            $formMonitoring = FrmMonitoring::where('nip', $findUserNip)
                ->where('status', 'Draft')
                ->latest('created_at')
                ->first();
            // cuma mau ngambil id
            $currentFormId = FrmMonitoring::where('nip', $findUserNip)
                ->where('status', 'Draft')
                ->pluck('id')
                ->first();

            $allDataSourceStatus = 0;
            $allAlertGrafanaConditionStatus = 0;


            $currentStoreDataStatus = FrmMonitoringStoreData::where('frm_monitoring_id', $currentFormId)->get();
            $currentVisualizationStatus = FrmMonitoringDashboard::where('frm_monitoring_id', $currentFormId)->get();
            $currentAccessDashboardStatus = FrmMonitoringDashboardAccess::where('frm_monitoring_id', $currentFormId)->get();
            $currentAlertStatus = FrmMonitoringAlert::where('frm_monitoring_id', $currentFormId)->get();
            $visualizationIdList = FrmMonitoringDashboard::where('frm_monitoring_id', $currentFormId)->pluck('id');
            $alertIdList = FrmMonitoringAlert::where('frm_monitoring_id', $currentFormId)->pluck('id');
            $alertGrafanaIdList = [];

            if (!$alertIdList->isEmpty()) {


                foreach ($alertIdList as $item) {
                    $alertGrafanaIdList = FrmMonitoringAlertGrafana::where('frm_monitoring_alert_id', $item)->pluck('id');
                }
            } else {
            }
            // dd($alertGrafanaIdList);
            foreach ($alertGrafanaIdList as $item) {
                $temp = FrmMonitoringAlertGrafanaCondition::where('frm_monitoring_alert_grafana_id', $item)->get();
                if ($temp->isEmpty()) {
                    $allAlertGrafanaConditionStatus = 1;
                    break;
                }
            }
            // dd($allAlertGrafanaConditionStatus);



            foreach ($visualizationIdList as $item) {
                $temp = FrmMonitoringDashboardDataSource::where('frm_monitoring_dashboard_id', $item)->get();
                if ($temp->isEmpty()) {
                    $allDataSourceStatus = 1;
                    break;
                }
            }


            $status = $newValidatedData['status'];
            if ($status == 'Submitted') {
                if ($currentStoreDataStatus->isEmpty() && $currentVisualizationStatus->isEmpty() && $currentAccessDashboardStatus->isEmpty() && $currentAlertStatus->isEmpty()) {
                    $request->session()->flash('alert', 'Form cannot be empty');
                }
                if ($currentVisualizationStatus->isEmpty() && !$currentAlertStatus->isEmpty()) {

                    if ($allAlertGrafanaConditionStatus == 1) {
                        $request->session()->flash('alert', 'No Grafana Condition');
                    } else {


                        $formMonitoring->update($newValidatedData);
                    }
                } else if (!$currentVisualizationStatus->isEmpty() && $currentAlertStatus->isEmpty()) {

                    if ($allDataSourceStatus == 1) {
                        $request->session()->flash('alert', 'No Visualization Data Source');
                    } else {

                        $formMonitoring->update($newValidatedData);
                    }
                } else if (!$currentVisualizationStatus->isEmpty() && !$currentAlertStatus->isEmpty()) {
                    if ($allDataSourceStatus == 1 && $allAlertGrafanaConditionStatus == 1) {
                        $request->session()->flash('alert', 'No Visualization Data Source and No Alert Grafana Condition');
                    } else if ($allDataSourceStatus == 0 && $allAlertGrafanaConditionStatus == 1) {
                        $request->session()->flash('alert', 'No Alert Grafana Condition');
                    } else if ($allDataSourceStatus == 1 && $allAlertGrafanaConditionStatus == 0) {
                        $request->session()->flash('alert', 'No Visualization Data Source');
                    } else {


                        $formMonitoring->update($newValidatedData);
                    }
                }
            } else {
                // $request->session()->flash('alert', 'a');
                // dd("hella");

                $formMonitoring->update($newValidatedData);
            }
            // $formMonitoring->update($newValidatedData);
            return redirect('/user/services/formMonitoring');
        }

        return redirect('/user/services/formMonitoring');
    }

    public function formAdmin()
    {
        return view('form_monitoring.formMonitoringAdmin');
    }

    public function getAdminFormMonitoringData()
    {

        $formMonitoring = FrmMonitoring::whereIn('status', ["Submitted", "Completed", "On progress"])
            ->orderBy('id', 'desc')
            ->get();

        return DataTables::of($formMonitoring)->make();
    }

    public function getAdminFormMonitoringSpecific($id)
    {
        $findUser = User::find(Auth::user()->id);
        $currentId = $id;
        $formMonitoring = FrmMonitoring::where('id', $id)->first();
        $currentDraftStoreData = FrmMonitoringStoreData::where('frm_monitoring_id', $id)->get();
        $currentDraftVisualization = FrmMonitoringDashboard::where('frm_monitoring_id', $id)->get();
        $currentDraftAccessDashboard = FrmMonitoringDashboardAccess::where('frm_monitoring_id', $id)->get();
        $currentDraftAlert = FrmMonitoringAlert::where('frm_monitoring_id', $id)->get();

        // enter status returner here

        $completedStatus = 0;

        // cek apakah ada yang nggak done
        $hasNotDoneStatus = $currentDraftStoreData->contains('status', 'NOT DONE');
        $hasNotDoneVisualizatonStatus = $currentDraftVisualization->contains('status', 'NOT DONE');
        $hasNotDoneAccessDashboardStatus = $currentDraftAccessDashboard->contains('status', 'NOT DONE');
        $hasNotDoneAlertStatus = $currentDraftAlert->contains('status', 'NOT DONE');

        if (!$hasNotDoneStatus && !$hasNotDoneVisualizatonStatus && !$hasNotDoneAccessDashboardStatus && !$hasNotDoneAlertStatus) {
            $completedStatus = 1;
        }



        return view('form_monitoring.formMonitoringAdminSpecific', compact('formMonitoring', 'currentDraftStoreData', 'currentDraftVisualization', 'currentDraftAccessDashboard', 'currentDraftAlert', 'completedStatus', 'findUser', 'currentId'));
    }

    public function adminFormMonitoringController(Request $request)
    {
        $validatedData = $request->validate([
            'status' => 'required',
            'pic' => 'required'
        ]);

        if ($validatedData['status'] === 'Completed') {
            $validatedData['finish_date'] = now()->format('Y-m-d H:i:s');
        }

        $formMonitoring = FrmMonitoring::where('id', $request->id);
        $id = $request->id;
        $formMonitoring->update($validatedData);
        return redirect('/network-monitoring/form-monitoring/getAdminFormMonitoringSpecific/' . $id);
    }

    public function adminFormMonitoringStatusSetter(Request $request, $id)
    {
        $validatedData = $request->validate([
            'status' => 'required',
            'pic_admin_form' => 'required'
        ]);

        $status = $validatedData['status'];
        $pic = $validatedData['pic_admin_form'];
        $updated = FrmMonitoring::findOrFail($id);
        $updated->status = $status;
        $updated->pic = $pic;
        $updated->save();

        return response()->json(['message' => 'Status updated']);
    }

    public function getAdminFormMonitoringStoreData($id)
    {


        $storeData = FrmMonitoringStoreData::with(['formMonitoring' => function ($query) {
            $query->select('id', 'status'); // Load only necessary fields
        }])->where('frm_monitoring_id', $id)->get();

        return DataTables::of($storeData)->addColumn('statusFormMonitoring', function ($row) {
            return $row->formMonitoring ? $row->formMonitoring->status : null;
        })

            ->make();
    }

    public function adminStoreDataController(Request $request, $id)
    {
        $validatedData = $request->validate([
            'admin_data_source' => 'required',
            'admin_source_address' => ['nullable', 'regex:/^((\d{1,2}|1\d\d|2[0-4]\d|25[0-5])\.){3}(\d{1,2}|1\d\d|2[0-4]\d|25[0-5])(\/([0-2]?[0-9]|3[0-2]))?$/'],
            'admin_port' => ['required', 'numeric', 'min:0', 'max:65535'],
            'admin_data_retention' => 'required|numeric|min:0',
            'admin_index_rotation' => 'required',
            'admin_time_field' => ['required', 'regex:/^[^\s]+$/'],
            'index_name' => 'required',
            'status' => 'required'
        ]);
        $data_source = $validatedData['admin_data_source'];
        $source_address = $validatedData['admin_source_address'];
        $port = $validatedData['admin_port'];
        $data_retention = $validatedData['admin_data_retention'];
        $index_rotation = $validatedData['admin_index_rotation'];
        $time_field = $validatedData['admin_time_field'];
        $index_name = $validatedData['index_name'];
        $status = $validatedData['status'];

        $updated = FrmMonitoringStoreData::findOrFail($id);
        $updated->data_source = $data_source;
        $updated->source_address = $source_address;
        $updated->port = $port;
        $updated->data_retention = $data_retention;
        $updated->index_rotation = $index_rotation;
        $updated->time_field = $time_field;
        $updated->index_name = $index_name;
        $updated->status = $status;
        $updated->save();

        return response()->json(['message' => 'Successfully updated store data status']);
    }

    public function adminStoreDataGetSpecific($id)
    {
        $editData = FrmMonitoringStoreData::findOrFail($id);
        return view('form_monitoring.formMonitoringAdminSpecific', ['editData' => $editData]);
    }

    public function adminStoreDataDefaultValue($id)
    {
        $specific = FrmMonitoringStoreData::find($id);
        return response()->json($specific);
    }

    public function getAdminFormMonitoringVisualization($id)
    {
        $visualization = FrmMonitoringDashboard::with(['formMonitoring' => function ($query) {
            $query->select('id', 'status'); // Load only necessary fields
        }])->where('frm_monitoring_id', $id)->get();

        return DataTables::of($visualization)
            ->addColumn('statusFormMonitoring', function ($row) {
                return $row->formMonitoring ? $row->formMonitoring->status : null;
            })
            ->make();
    }




    public function getAdminFormMonitoringDataSourceDashboard($id)
    {
        $finale = FrmMonitoringDashboardDataSource::with(['storeData' => function ($query) {
            $query->select('id', 'index_name'); // Load only necessary fields
        }])->where('frm_monitoring_dashboard_id', $id)->get();

        return DataTables::of($finale)
            ->addColumn('index_name', function ($data) {
                return $data->storeData ? $data->storeData->index_name : '-';
            })
            ->make();
    }


    public function getAdminFormMonitoringDataSourceDashboardVisualization($id)
    {
        $finale = FrmMonitoringDashboardDataSource::with(['storeData' => function ($query) {
            $query->select('id', 'index_name'); // Load only necessary fields
        }])->where('frm_monitoring_dashboard_id', $id)->get();

        return DataTables::of($finale)
            ->addColumn('index_name', function ($data) {
                return $data->storeData ? $data->storeData->index_name : '-';
            })
            ->make();
    }

    public function getAdminFormMonitoringAccessDashboard($id)
    {


        $accessDashboard = FrmMonitoringDashboardAccess::with(['formMonitoring', 'dashboardMonitoring' => function ($query) {
            $query->select('id', 'status'); // Load only necessary fields
        }])->with(['dashboardMonitoring' => function ($query) {
            $query->select('id', 'dashboard_name'); // Load only necessary fields
        }])->where('frm_monitoring_id', $id)->get();

        return DataTables::of($accessDashboard)
            ->addColumn('statusFormMonitoring', function ($row) {
                return $row->formMonitoring ? $row->formMonitoring->status : null;
            })
            ->addColumn('dashboard_name', function ($data) {
                return $data->dashboardMonitoring ? $data->dashboardMonitoring->dashboard_name : '-';
            })
            ->make();
    }

    public function adminVisualizationStatusSetter(Request $request, $id)
    {
        $validatedData = $request->validate([
            'status' => 'required'
        ]);

        $status = $validatedData['status'];
        $updated = FrmMonitoringDashboard::findOrFail($id);
        $updated->status = $status;
        $updated->save();

        return response()->json(['message' => 'Succesffully updated visualization status']);
    }

    public function adminAccessDashboardStatusSetter(Request $request, $id)
    {
        $validatedData = $request->validate([
            'status' => 'required'
        ]);

        $status = $validatedData['status'];
        $updated = FrmMonitoringDashboardAccess::findOrFail($id);
        $updated->status = $status;
        $updated->save();

        return response()->json(['message' => 'Succesfully updated access dashboard status']);
    }


    public function adminSetStatus(Request $request, $id)
    {
        $validatedData = $request->validate([
            'status' => 'required'
        ]);

        $status = $validatedData['status'];
        $updated = FrmMonitoring::findOrFail($id);
        $updated->status = $status;
        $updated->save();
        return response()->json(['message' => 'Record updated successfully']);
    }

    public function getCurrentUser()
    {
        $findUser = User::find(Auth::user()->id);

        return response()->json($findUser);
    }

    public function getIndexName($id)
    {
        $adminStoreData = FrmMonitoringStoreData::where('id', $id)->first()->get();

        return response()->json($adminStoreData);
    }

    public function getAdminFormMonitoringVisualizationDefaultValue($id)
    {
        $specific = FrmMonitoringDashboard::find($id);
        return response()->json($specific);
    }


    public function getAdminFormMonitoringAlert($id)
    {

        $alert = FrmMonitoringAlert::with(['formMonitoring' => function ($query) {
            $query->select('id', 'status'); // Load only necessary fields
        }])->where('frm_monitoring_id', $id)->get();

        return DataTables::of($alert)->addColumn('statusFormMonitoring', function ($row) {
            return $row->formMonitoring ? $row->formMonitoring->status : null;
        })

            ->make();
    }

    public function getAdminFormMonitoringAlertLogstash($id)
    {
        $finale = FrmMonitoringAlertLogstash::with(['storeData' => function ($query) {
            $query->select('id', 'index_name'); // Load only necessary fields
        }])->where('frm_monitoring_alert_id', $id)->get();

        $data = $finale->map(function ($item) {
            return [
                'severity' => $item->severity,
                'send_to_email' => $item->send_to_email,
                'send_to_telegram_channel' => $item->send_to_telegram_channel,
                'token_telegram' => $item->token_telegram,
                'condition' => $item->condition,
                'description' => $item->description,
                'frm_monitoring_store_data_id' => optional($item->storeData)->index_name ?? '-',

            ];
        });

        return response()->json(['data' => $data->first()]);
    }

    public function getAdminFormMonitoringAlertGrafana($id)
    {
        $finale = FrmMonitoringAlertGrafana::with(['dashboardMonitoring' => function ($query) {
            $query->select('id', 'dashboard_name'); // Load only necessary fields
        }])->where('frm_monitoring_alert_id', $id)->get();

        $data = $finale->map(function ($item) {
            return [
                'severity' => $item->severity,
                'graphic_name' => $item->graphic_name,
                'send_to_email' => $item->send_to_email,
                'send_to_telegram_channel' => $item->send_to_telegram_channel,
                'evaluate_every' => $item->evaluate_every,
                'for_duration' => $item->for_duration,
                'no_data_handling' => $item->no_data_handling,
                'error_handling' => $item->error_handling,
                'frm_monitoring_dashboard_id' => optional($item->dashboardMonitoring)->dashboard_name ?? '-',
            ];
        });

        return response()->json(['data' => $data->first()]);
    }

    public function getAdminFormMonitoringAlertApi($id)
    {
        $finale = FrmMonitoringAlertApi::with(['storeData' => function ($query) {
            $query->select('id', 'index_name'); // Load only necessary fields
        }])->where('frm_monitoring_alert_id', $id)->get();

        $data = $finale->map(function ($item) {
            return [
                'severity' => $item->severity,
                'send_to_email' => $item->send_to_email,
                'send_to_telegram_channel' => $item->send_to_telegram_channel,
                'token_telegram' => $item->token_telegram,
                'condition' => $item->condition,
                'description' => $item->description,
                'frm_monitoring_store_data_id' => optional($item->storeData)->index_name ?? '-',

            ];
        });

        return response()->json(['data' => $data->first()]);
    }

    public function getAdminFormMonitoringAlertGrafanaCondition($id)
    {
        $alertGrafana = FrmMonitoringAlertGrafana::where('frm_monitoring_alert_id', $id)->pluck('id')->first();

        $alertGrafanaCondition = FrmMonitoringAlertGrafanaCondition::where('frm_monitoring_alert_grafana_id', $alertGrafana)->get();
        return DataTables::of($alertGrafanaCondition)->make();
    }







    public function adminAlertStatusSetter(Request $request, $id)
    {
        $validatedData = $request->validate([
            'status' => 'required'
        ]);

        $status = $validatedData['status'];
        $updated = FrmMonitoringAlert::findOrFail($id);
        $updated->status = $status;
        $updated->save();

        return response()->json(['message' => 'Succesfully updated alert status']);
    }
}