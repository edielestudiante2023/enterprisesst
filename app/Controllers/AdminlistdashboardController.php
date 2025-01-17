<?php

namespace App\Controllers;

use App\Models\DashboardItemModel;
use CodeIgniter\Controller;

class AdminlistdashboardController extends Controller
{
    public function listitemdashboard()
    {
        $model = new DashboardItemModel();
        $data['items'] = $model->findAll();
        return view('consultant/listitemdashboard', $data);
    }

    public function additemdashboard()
    {
        return view('consultant/additemdashboard');
    }

    public function additemdashboardpost()
    {
        $model = new DashboardItemModel();
        $data = $this->request->getPost();
        $model->save($data);
        return redirect()->to(site_url('consultant/listitemdashboard'));
    }

    public function edititemdashboar($id)
    {
        $model = new DashboardItemModel();
        $item = $model->find($id);
        if(!$item) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Item not found');
        }
        $data['item'] = $item;
        return view('consultant/edititemdashboar', $data);
    }

    public function editpostitemdashboar($id)
    {
        $model = new DashboardItemModel();
        $data = $this->request->getPost();
        $model->update($id, $data);
        return redirect()->to(site_url('consultant/listitemdashboard'));
    }

    public function deleteitemdashboard($id)
    {
        $model = new DashboardItemModel();
        $model->delete($id);
        return redirect()->to(site_url('consultant/listitemdashboard'));
    }
}
