<?php

require_once ('config.php');
require_once ('error_handler.php');

class Chat
{
    private $mMysqli;

    function __construct()
    {
        $this->mMysqli = new mysqli(DB_HOST,DB_USER,DB_PASSWORD,DB_DATABASE);
    }
    public function __destruct()
    {
        $this->mMysqli->close();
    }

    public function deleteMessages()
    {
        //build the SQL query that adds a new message to the server
        $query = 'TRUNCATE TABLE chat';
        //execute the SQL query
        $result = $this->mMysqli->query($query);
    }
    public function postMessage($name, $message, $color)
    {
        $name = $this->mMysqli->real_escape_string($name);
        $message = $this->mMysqli->real_escape_string($message);
        $color = $this->mMysqli->real_escape_string($color);

        $query = 'INSERT INTO chat(posted_on, user_name, message, color)
                  VALUES (NOW(), "'.$name.'", "'.$message.'","'.$color.'")';
        $result = $this->mMysqli->query($query);
    }
    public function retrieveNewMessages($id=0)
    {
        $id = $this->mMysqli->real_escape_string($id);
        if($id > 0){
            $query = 'SELECT chat_id, user_name, message, color,
                      DATE_FORMAT(posted_on, "%Y-%m-%d %H:%i:%s")
                      AS posted_on
                      FROM chat WHERE chat_id > '.$id.'
                      ORDER BY chat_id ASC';
        } else {
            $query = 'SELECT chat_id, user_name, message, color,
                     posted_on FROM
                     (SELECT chat_id, user_name, message, color,
                     DATE_FORMAT(posted_on, "%Y-%m-%d %H:%i:%s")
                     AS posted_on
                     FROM chat
                     ORDER BY chat_id DESC
                     LIMIT 50) AS Last50
                     ORDER BY chat_id ASC';
        }
        $result = $this->mMysqli->query($query);
        $response = array();
        $response['clear'] = $this->isDatabaseCleared($id);
        $response['messages'] = array();

        if($result->num_rows){

            while($row = $result->fetch_array(MYSQLI_ASSOC)){
                $message = array();
                $message['id'] = $row['chat_id'];
                $message['color'] = $row['color'];
                $message['name'] = $row['user_name'];
                $message['time'] = $row['posted_on'];
                $message['message'] = $row['message'];
                array_push($response['messages'],$message);
            }
            $result->close();
        }
        return $response;
    }
    private function isDatabaseCleared($id)
    {
        if($id > 0){
            $check_clear = 'SELECT count(*) old
                            FROM chat WHERE
                            chat_id <=' .$id;
            $result = $this->mMysqli->query($check_clear);
            $row = $result->fetch_array(MYSQLI_ASSOC);

            if($row['old'] == 0)
                return 'true';
            return 'false';
        }
        return 'true';
    }
}
?>