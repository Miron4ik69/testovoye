<?php 

class Data
{
    private $conn;

    public function __construct($pdo)
    {
        $this->conn = $pdo;
    }

    public function add($param)
    {
        $filename = $this->UploadImage($_FILES['image']);
        try{
            $statment = $this->conn->prepare("insert into ads(`text`, `price`, `amount`) values('{$param['text']}', '{$param['price']}', '{$param['amount']}')");
            $statment->execute();

            $stmt = $this->conn->prepare("insert into images(`image_name`, `ad_id`) values('{$filename}', LAST_INSERT_ID())");
            $stmt->execute();
        
        } catch(PDOException $e){
            echo $e->getMessage();
        }
    }

    public function api()
    {
        $getAD = $this->getAd();
        foreach($getAD as $value){
            $this->updateRequest($value);
            $arr = [
                'text' => $value->text,
                'image' => $_SERVER['HTTP_HOST'] . "/uploads/" . $value->image_name
            ];
            echo json_encode($arr); 
            exit;
        }
    }

    private function updateRequest($value)
    {
        try {
            $num = $value->request;
            $num++;
            $statment = $this->conn->prepare("
            
                UPDATE `ads` SET `request` = {$num} WHERE `id` = {$value->ad_id} 
            
            ");

            $statment->execute();
        }catch(PDOException $e){
            echo $e->getMessage();
        }
    }

    private function getAD()
    {
       try{
            $statment = $this->conn->prepare("
            
                SELECT * FROM ads JOIN images ON ads.id = images.ad_id WHERE ads.request < ads.amount ORDER BY ads.price DESC LIMIT 1
            
            ");

            $statment->execute();
            
            return $statment->fetchAll(PDO::FETCH_CLASS);
       }catch(PDOException $e){
            echo $e->getMessage();
       }
    }

    private function UploadImage($image)
    {
        $extension = pathinfo($image['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . "." . $extension;
        move_uploaded_file($image['tmp_name'], "uploads/" . $filename);
        return $filename;
    }
}