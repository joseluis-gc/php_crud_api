<?php

class Controller{


    private TaskGateWay $gateWay;

    public function __construct(TaskGateWay $gateWay)
    {
        $this->gateWay = $gateWay;

    }

    public function processRequest(string $method, ?string $id) : void{
        if($id === null)
        {
            if($method == "GET")
            {
                echo json_encode($this->gateWay->getAll());
            }
            elseif($method == "POST")
            {
               $data = (array) json_decode(file_get_contents("php://input"),true);
               $errors = $this->getValidationErrors($data);

               if(!empty($errors))
               {
                   $this->respondUnprocessableEntity($errors);
                   return;
               }

               $id = $this->gateWay->create($data);
               $this->respondCreated($id);
            }
            else
            {
               $this->respondMethodNotAllowed("GET, POST");
            }
        }
        else
        {
            $task = $this->gateWay->get($id);
            if($task===false){
                $this->respond404NotFound($id);
            }

            switch ($method)
            {
                case "GET":
                    echo json_encode($task);
                    break;


                case "PATCH":

                    $data = (array) json_decode(file_get_contents("php://input"),true);
                    $errors = $this->getValidationErrors($data, false);

                    if(!empty($errors))
                    {
                        $this->respondUnprocessableEntity($errors);
                        return;
                    }

                    $rows = $this->gateWay->update($id, $data);
                    echo json_encode(["message" => "Task updated", "rows" => $rows]);


                    break;


                case  "DELETE":
                    $rows = $this->gateWay->delete($id);
                    echo json_encode(["message" => "Task deleted", "rows" => $rows]);
                    break;


                default:
                    $this->respondMethodNotAllowed("GET,PATH,DELETE");
                    break;
            }
        }
    }



    private function respondMethodNotAllowed(string $allowed_methods) :void
    {
        http_response_code(405);
        header("Allow: $allowed_methods");
    }

    private function respond404NotFound(string $id):void
    {
        http_response_code(404);
        echo json_encode(["message"=>"Task with ID: $id Not found"]);
    }

    private function respondCreated(string $id): void
    {
        http_response_code(201);
        echo  json_encode(["message"=>"Record Created", "id"=>$id]);
    }

    private function respondUnprocessableEntity(array $errors): void
    {
        http_response_code(422);
        echo json_encode(["errors"=>$errors]);
    }

    private function getValidationErrors(array $data, bool $is_new = true): array
    {
        $errors = [];

        if($is_new && empty($data['name']))
        {
            $errors[] = "Name is a required field";
        }

        if(!empty($data['priority']))
        {
            if(filter_var($data['priority'], FILTER_VALIDATE_INT) === false)
            {
                $errors[] = "Priority must be an integer value";
            }
        }

        return  $errors;
    }

}




