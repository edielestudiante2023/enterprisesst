<?php

namespace App\Controllers;

use App\Models\ReportTypeModel;
use CodeIgniter\Controller;

class ReportTypeController extends Controller
{
    public function index()
    {
        $model = new ReportTypeModel();
        $data['reportTypes'] = $model->findAll();

        return view('consultant/list_report_types', $data);
    }

    public function listReportTypes()
    {
        $model = new ReportTypeModel();
        $reportTypes = $model->findAll();

        return view('consultant/list_report_types', ['reportTypes' => $reportTypes]);
    }

    public function addReportType()
    {
        return view('consultant/add_report_type');
    }

    public function addReportTypePost()
    {
        $model = new ReportTypeModel();
        $data = [
            'report_type' => $this->request->getVar('report_type'),
        ];
        $model->save($data);

        return redirect()->to('/listReportTypes');
    }

    public function edit($id)
    {
        $model = new ReportTypeModel();
        $reportType = $model->find($id);

        return view('consultant/edit_report_type', ['reportType' => $reportType]);
    }

    public function editPost($id)
    {
        $model = new ReportTypeModel();
        $data = [
            'report_type' => $this->request->getVar('report_type'),
        ];
        $model->update($id, $data);

        return redirect()->to('/listReportTypes');
    }

    public function delete($id)
    {
        $model = new ReportTypeModel();
        $model->delete($id);

        return redirect()->to('/listReportTypes');
    }


}
