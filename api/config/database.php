<?php
require 'vendor/autoload.php';

use Google\Cloud\SecretManager\V1\SecretManagerServiceClient;

class Database {
    private $host = "localhost";
    private $db_name = "hospital_db";
    private $username;
    private $password;
    public $conn;

    private $gcp_project_id;
    private $username_secret;
    private $password_secret;

    public function __construct() {
        $this->gcp_project_id   = "your-gcp-project-id"
        $this->username_secret  = "db-username"
        $this->password_secret  = "db-password"
    }

    private function getSecret($secretName) {
        $client = new SecretManagerServiceClient();
        $name = $client->secretVersionName($this->gcp_project_id, $secretName, 'latest');
        $response = $client->accessSecretVersion($name);
        return $response->getPayload()->getData();
    }

    public function getConnection() {
        $this->conn = null;

        // Fetch secrets from GCP Secret Manager
        try {
            $this->username = $this->getSecret($this->username_secret);
            $this->password = $this->getSecret($this->password_secret);
        } catch(Exception $e) {
            echo "Secret Manager error: " . $e->getMessage();
            return null;
        }

        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->db_name}",
                $this->username,
                $this->password
            );
            $this->conn->exec("set names utf8");
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>
