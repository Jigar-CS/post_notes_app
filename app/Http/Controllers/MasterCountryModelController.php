<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Validator;
use App\Models\MasterCountryModel;
use App\Http\Controllers\AuthMiddleware;
class MasterCountryModelController extends Controller {
    public function createCountry(Request $request) {
        $auth = AuthMiddleware::authenticate($request);
        if (!$auth) { return response()->json(['status' => 401, 'error' => 'Authorization required.'], 401); }
        $valid = Validator::make($request->all(), [
            "country_name" => "required"
        ]);
        if ($valid->fails()) {
            return response()->json(['status' => 400, 'error' => $valid->errors()], 400);
        } else {
            // Only admins can create countries
            if ($auth['role_id'] != 1) { return response()->json(['status' => 403, 'error' => 'Forbidden: only administrators can create countries.'], 403); }
            // Only admins can create countries
            if ($auth['role_id'] != 1) { return response()->json(['status' => 403, 'error' => 'Forbidden: only administrators can create countries.'], 403); }
            $country = new MasterCountryModel();
            $country->country_name = $request->input('country_name');
            $country->country_status = 1;
            $country->created_by = $auth['user_id'];
            try {
                $result = $country->save();
                if ($result) {
                    return response()->json(['status' => 200, 'data' => $result], 200);
                } else {
                    return response()->json(['status' => 400, 'error' => 'Save returned false.'], 400);
                }
            } catch (\Exception $e) {
                return response()->json(['status' => 400, 'error' => $e->getMessage()], 400);
            }
        }
    }
    public function fetchAllCountries(Request $request) {
        $auth = AuthMiddleware::authenticate($request);
        if (!$auth) { return response()->json(['status' => 401, 'error' => 'Authorization required.'], 401); }
        $valid = Validator::make($request->all(), [
            'offset' => 'required|integer',
            'limit' => 'required|integer'
        ]);
        if ($valid->fails()) {
            return response()->json(['status' => 400, 'error' => $valid->errors()], 400);
        } else {
            $data = array();
            $data["offset"] = $request->input("offset");
            $data["limit"] = $request->input("limit");
            if ($request->has('search') && $request->input('search') != "") { $data['search'] = $request->input('search'); }
            try {
                $query = MasterCountryModel::query();
                if (isset($data['search'])) { $query->where('country_name', 'LIKE', '%' . $data['search'] . '%'); }
                if (isset($data['offset'])) { $query->skip($data['offset']); }
                if (isset($data['limit'])) { $query->take($data['limit']); }
                $countries = $query->get();
                return response()->json(['status' => 200, 'count' => count($countries), 'data' => $countries], 200);
            } catch (\Exception $e) {
                return response()->json(['status' => 400, 'error' => $e->getMessage()], 400);
            }
        }
    }
    public function fetchSingleCountry(Request $request) {
        $auth = AuthMiddleware::authenticate($request);
        if (!$auth) { return response()->json(['status' => 401, 'error' => 'Authorization required.'], 401); }
        $valid = Validator::make($request->all(), [
            "country_id" => "required"
        ]);
        if ($valid->fails()) {
            return response()->json(['status' => 400, 'error' => $valid->errors()], 400);
        } else {
            try {
                $country = MasterCountryModel::where('country_id', $request->input('country_id'))->first();
                return response()->json(['status' => 200, 'data' => $country], 200);
            } catch (\Exception $e) {
                return response()->json(['status' => 400, 'error' => $e->getMessage()], 400);
            }
        }
    }
    public function updateCountry(Request $request) {
        $auth = AuthMiddleware::authenticate($request);
        if (!$auth) { return response()->json(['status' => 401, 'error' => 'Authorization required.'], 401); }
        $valid = Validator::make($request->all(), [
            "country_id" => "required"
        ]);
        if ($valid->fails()) {
            return response()->json(['status' => 400, 'error' => $valid->errors()], 400);
        } else {
            // Only admins can update countries
            if ($auth['role_id'] != 1) { return response()->json(['status' => 403, 'error' => 'Forbidden: only administrators can update countries.'], 403); }
            // Only admins can update countries
            if ($auth['role_id'] != 1) { return response()->json(['status' => 403, 'error' => 'Forbidden: only administrators can update countries.'], 403); }
            $country = new MasterCountryModel();
            $newrequest = $request->except(['country_id']);
            $newrequest['updated_by'] = $auth['user_id'];
            try {
                $result = $country->where('country_id', $request->input('country_id'))->update($newrequest);
                if ($result) {
                    return response()->json(['status' => 200, 'data' => $result], 200);
                } else {
                    return response()->json(['status' => 400, 'error' => "No rows updated or something went wrong."], 400);
                }
            } catch (\Exception $e) {
                return response()->json(['status' => 400, 'error' => $e->getMessage()], 400);
            }
        }
    }
    public function deleteCountry(Request $request) {
        $auth = AuthMiddleware::authenticate($request);
        if (!$auth) { return response()->json(['status' => 401, 'error' => 'Authorization required.'], 401); }
        $valid = Validator::make($request->all(), [
            "country_id" => "required"
        ]);
        if ($valid->fails()) {
            return response()->json(['status' => 400, 'error' => $valid->errors()], 400);
        } else {
            // Only admins can delete countries
            if ($auth['role_id'] != 1) { return response()->json(['status' => 403, 'error' => 'Forbidden: only administrators can delete countries.'], 403); }
            // Only admins can delete countries
            if ($auth['role_id'] != 1) { return response()->json(['status' => 403, 'error' => 'Forbidden: only administrators can delete countries.'], 403); }
            $country = new MasterCountryModel();
            $request->request->add(['country_status' => 0, 'updated_by' => $auth['user_id']]);
            $newrequest = $request->except(['country_id']);
            try {
                $result = $country->where('country_id', $request->input('country_id'))->update($newrequest);
                if ($result) {
                    return response()->json(['status' => 200, 'data' => $request->all()], 200);
                } else {
                    return response()->json(['status' => 400, 'error' => 'No rows updated or something went wrong.'], 400);
                }
            } catch (\Exception $e) {
                return response()->json(['status' => 400, 'error' => $e->getMessage()], 400);
            }
        }
    }
}