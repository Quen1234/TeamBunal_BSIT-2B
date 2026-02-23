<?php

namespace App\Controllers;
use CodeIgniter\Controller;
use App\Models\LogModel;
use App\Models\PersonModel;


class Person extends Controller
{
    public function index() {
        $model = new PersonModel();
        $data['person'] = $model->findAll();
        return view('person/index', $data);
    }

    public function save() {
      
        $name     = $this->request->getPost('name1');
        $bday    = $this->request->getPost('bday');
        $address = $this->request->getPost('address');
        $userModel = new PersonModel();
        $logModel = new LogModel();


        $data = [
            'name'       => $name,
            'address'      => $address,
            'bday'       => $bday
        ];

        if ($userModel->insert($data)) {
            $logModel->addLog('New User added: ' . $name, 'ADD');
            return $this->response->setJSON(['status' => 'success']);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to save user']);
        }
    }

    public function update() {
        $model = new PersonModel();
        $logModel = new LogModel();

        $userId   = $this->request->getPost('id');
        $name     = $this->request->getPost('name');
        $bday    = $this->request->getPost('bday');
        $address = $this->request->getPost('address');
       
        $userData = [
            'name'       => $name,
            'bday'      => $bday,
            'address'       => $address,
      
        ];

        if (!empty($password)) {
            $userData['password'] = password_hash('password', 'PASSWORD_BCRYPT');
        }

        if ($model->update($userId, $userData)) {
            $logModel->addLog('User updated: ' . $name, 'UPDATED');
            return $this->response->setJSON(['success' => true, 'message' => 'User updated successfully.']);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Error updating user.']);
        }
    }

    public function edit($id) {
        $model = new PersonModel();
        $user = $model->find($id);

        if ($user) {
            return $this->response->setJSON(['data' => $user]);
        } else {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'User not found']);
        }
    }

    public function delete($id) {
        $model = new PersonModel();
        $logModel = new LogModel();

        if ($model->delete($id)) {
            $logModel->addLog('Deleted user ID: ' . $id, 'DELETED');
            return $this->response->setJSON(['success' => true, 'message' => 'User deleted successfully.']);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete user.']);
        }
    }


    public function fetchRecords() {
        $request = service('request');
        $model = new PersonModel();

        $start = $request->getPost('start') ?? 0;
        $length = $request->getPost('length') ?? 10;
        $searchValue = $request->getPost('search')['value'] ?? '';

        $totalRecords = $model->countAll();
        $result = $model->getRecords($start, $length, $searchValue);

        $data = [];
        $counter = $start + 1;
        foreach ($result['data'] as $row) {
            $data[] = [
                'row_number' => $counter++,
                'id'         => $row['id'],
                'name'       => $row['name'] ?? '',
                'bday'      => $row['bday'] ?? '',
                'address'      => $row['address'] ?? '',
           
            ];
        }

        return $this->response->setJSON([
            'draw'            => intval($request->getPost('draw')),
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $result['filtered'],
            'data'            => $data,
        ]);
    }
    
    
    
}