<?php
namespace Src\Routes;



use PH7\JustHttp\StatusCode;
use PH7\PhpHttpResponseHeader\Http;
use Src\Service\User;
use Src\Exceptions\InvalidValidationException;

enum UserActionRoute: string
{
    case CREATE = 'create';
    case RETRIEVE = 'retrieve';
    case RETRIEVE_ALL = 'retrieveAll';
    case REMOVE = 'remove';
    case UPDATE = 'update';

    //this getResponse methods interact with the User class based on the action value passed as query params
    public function getResponse(): string
    {
        // Get input data and decode it as payload
        $data = file_get_contents("php://input");
        $payload = json_decode($data);

        // Initialize a User instance fom the business logic.
        $user = new User();
        
        // Set userUuid from the query parameter in the url if available
        $userUuid = $_REQUEST['id'] ?? null;

        try {
            // Match the action and call the corresponding method
            //self points to the enums UserActionRoute then get map to the appropriate method in the User class.
            //for example if the  action = retrieve; $this will be UserActionRoute::RETRIEVE
            $response = match ($this) {
                self::CREATE => $user->create($payload),
                self::RETRIEVE_ALL => $user->retrieveAll(),
                self::RETRIEVE => $user->retrieve($userUuid),
                self::UPDATE => $user->update($payload),
               // self::REMOVE => $user->remove($userId),

               //logic for post method
                self::REMOVE => $user->remove($payload)
            };
        } catch (InvalidValidationException $e) {
            // Handle errors and set HTTP status to BAD_REQUEST
            Http::setHeadersByCode(StatusCode::BAD_REQUEST);
            $response = [
                'errors' => [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode()
                ]
            ];
        }

        return json_encode($response);
    }
}

// Determine the action based on query parameters then execute the getResponse()
$action = $_REQUEST['action'] ?? null;

$userActionRoute = userActionRoute::tryFrom($action);
if ($userActionRoute){
    echo $userActionRoute->getResponse();
}  else {
    require_once "route-not-found.php";
}


























