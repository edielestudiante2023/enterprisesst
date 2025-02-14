<?php

namespace App\Controllers;

use App\Models\MatrizModel;
use App\Models\ClientModel;
use CodeIgniter\Controller;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class MatricesController extends Controller
{
    public function list()
    {
        $model = new MatrizModel();
        $clientModel = new ClientModel();
        $data['matrices'] = $model->findAll();
        $data['clients'] = $clientModel->findAll();

        // Check if export to Excel was requested
        if ($this->request->getGet('export') === 'excel') {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set headers
            $sheet->setCellValue('A1', 'ID');
            $sheet->setCellValue('B1', 'Tipo');
            $sheet->setCellValue('C1', 'Descripción');
            $sheet->setCellValue('D1', 'Observaciones');
            $sheet->setCellValue('E1', 'Enlace');
            $sheet->setCellValue('F1', 'Cliente');

            // Style headers
            $sheet->getStyle('A1:F1')->getFont()->setBold(true);
            $sheet->getStyle('A1:F1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF808080');
            $sheet->getStyle('A1:F1')->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);

            // Add data
            $row = 2;
            foreach ($data['matrices'] as $matriz) {
                $clientName = '';
                foreach ($data['clients'] as $client) {
                    if ($client['id_cliente'] == $matriz['id_cliente']) {
                        $clientName = $client['nombre_cliente'];
                        break;
                    }
                }

                $sheet->setCellValue('A' . $row, $matriz['id_matriz']);
                $sheet->setCellValue('B' . $row, $matriz['tipo']);
                $sheet->setCellValue('C' . $row, $matriz['descripcion']);
                $sheet->setCellValue('D' . $row, $matriz['observaciones']);
                $sheet->setCellValue('E' . $row, $matriz['enlace']);
                $sheet->setCellValue('F' . $row, $clientName);
                $row++;
            }

            // Auto size columns
            foreach (range('A', 'F') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Create writer and output file
            $writer = new Xlsx($spreadsheet);
            $filename = 'matrices_' . date('Y-m-d_H-i-s') . '.xlsx';

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
            exit();
        }

        return view('consultant/list_matrices', $data);
    }

    public function add()
    {
        $clientModel = new ClientModel();
        $data['clients'] = $clientModel->findAll();
        return view('consultant/add_matrices', $data);
    }

    public function addPost()
    {
        $model = new MatrizModel();
        $data = [
            'tipo' => $this->request->getPost('tipo'),
            'descripcion' => $this->request->getPost('descripcion'),
            'observaciones' => $this->request->getPost('observaciones'),
            'enlace' => $this->request->getPost('enlace'),
            'id_cliente' => $this->request->getPost('id_cliente'),
        ];
        $model->save($data);
        return redirect()->to('/matrices/list')->with('msg', 'Dashboard agregado exitosamente');
    }

    public function edit($id)
    {
        $model = new MatrizModel();
        $clientModel = new ClientModel();
        $data['matrices'] = $model->find($id);
        $data['clients'] = $clientModel->findAll();
        return view('consultant/edit_matrices', $data);
    }

    public function editPost($id)
    {
        $model = new MatrizModel();
        $data = [
            'tipo' => $this->request->getPost('tipo'),
            'descripcion' => $this->request->getPost('descripcion'),
            'observaciones' => $this->request->getPost('observaciones'),
            'enlace' => $this->request->getPost('enlace'),
            'id_cliente' => $this->request->getPost('id_cliente'),
        ];
        $model->update($id, $data);
        return redirect()->to('/matrices/list')->with('msg', 'Dashboard actualizado exitosamente');
    }

    public function delete($id)
    {
        $model = new MatrizModel();
        $model->delete($id);
        return redirect()->to('/matrices/list')->with('msg', 'Dashboard eliminado exitosamente');
    }
}
