<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Controller;
use App\Models\LogModel;
use App\Models\TeamModel;

class Team extends Controller
{
    public function index(){
        $model = new TeamModel();
        $data['team'] = $model->findAll();
        return view('team/index', $data);
    }

//working on this one

    public function save(){
        $name = $this->request->getPost('name');
        $bday = $this->request->getPost('bday');

        $userModel = new \App\Models\TeamModel();
        $logModel = new LogModel();

        $data = [
            'name'       => $name,
            'bday'      => $bday
        ];

        if ($userModel->insert($data) !== false) {
            $logModel->addLog('New Person has been added: ' . $name, 'ADD');
            return $this->response->setJSON(['status' => 'success']);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to save person']);
        }
    }

//update is fixed
 public function update(){
    $model = new TeamModel(); 
    $logModel = new LogModel();

    $userId = $this->request->getPost('id');

    $data = [
        'name' => $this->request->getPost('name'),
        'bday' => $this->request->getPost('bday'),
    ];

    if ($model->update($userId, $data)) {
        $logModel->addLog('Team updated: ' . $data['name'], 'UPDATED');

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Team updated successfully!.'
        ]);
    }

    return $this->response->setJSON([
        'success' => false,
        'message' => 'Error updating team!.'
    ]);
}

    public function edit($id){
        $model = new TeamModel();
    $user = $model->find($id); // Fetch user by ID

    if ($user) {
        return $this->response->setJSON(['data' => $user]); // Return user data as JSON
    } else {
        return $this->response->setStatusCode(404)->setJSON(['error' => 'User not found']);
    }
}

public function delete($id = null){
    $model = new TeamModel();
    $logModel = new LogModel();
    
    // Get deletion criteria from request (either id or name)
    $deleteId = $id ?? $this->request->getPost('id');
    $deleteName = $this->request->getPost('name');
    
    // Validate that we have something to delete by
    if (!$deleteId && !$deleteName) {
        return $this->response->setJSON(['success' => false, 'message' => 'No ID or name provided.']);
    }
    
    // Delete by ID or name
    $deleted = $deleteId ? $model->delete($deleteId) : $model->where('name', $deleteName)->delete();
    
    if ($deleted) {
        $logModel->addLog('Team deleted: ' . ($deleteName ?? $deleteId), 'DELETE');
        return $this->response->setJSON(['success' => true, 'message' => 'Team deleted successfully.']);
    } else {
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete team.']);
    }
}

//working on this one
//working on this one
public function fetchRecords(){
    $request = service('request');
    $model = new \App\Models\TeamModel();

    $start = $request->getPost('start') ?? 0;
    $length = $request->getPost('length') ?? 10;
    $searchValue = $request->getPost('search')['value'] ?? '';

    $totalRecords = $model->countAll();
    $result = $model->getRecords($start, $length, $searchValue);

    $data = [];
    $counter = $start + 1;
    foreach ($result['data'] as $row) {
        $row['row_number'] = $counter++;
        $data[] = $row;
    }

    return $this->response->setJSON([
        'draw' => intval($request->getPost('draw')),
        'recordsTotal' => $totalRecords,
        'recordsFiltered' => $result['filtered'],
        'data' => $data,
    ]);
}
}