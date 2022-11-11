<?php
namespace API\models;

use API\core\Database;
use PDO;

class ProductModel
{
    private PDO $conn;

    public function __construct(Database $database)
    {
        $this->conn = $database->getConnection();
    }

    public function getAllProducts(): array
    {
        $sql = 'SELECT *
                FROM products
                ORDER BY type';
        $stmt = $this->conn->query($sql);
        $data = [];
        while ($row = $stmt->fetchAll(PDO::FETCH_ASSOC)) {
            $data['products'] = $row;
        }

        return $data;
    }

    public function findOne($sku): array|false
    {
        $sql = 'SELECT *
                FROM products
                WHERE sku = :sku';
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':sku', $sku, PDO::PARAM_STR);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findById($id): array|false
    {
        $sql = 'SELECT *
                FROM products
                WHERE id = :id';
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function deleteById($id)
    {
        $sql = 'DELETE
                FROM products
                WHERE id = :id';
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $id;
    }

    public function createProduct(array $data)
    {
        $attrs = ['sku', 'name', 'price', 'description', 'type', 'size', 'weight', 'height', 'width', 'length'];
        $keys = array_keys($data);
        if (!empty($keys)) {
            $sql = 'INSERT INTO products
                    ('.implode(',', $keys).') 
                    VALUES ( :'.implode(',:', $keys).')';
            $stmt = $this->conn->prepare($sql);
            foreach ($keys as $key) {
                $stmt->bindValue(":{$key}", $data[$key]);
            }
            $stmt->execute();

            return $this->conn->lastInsertId();
        }
        http_response_code(422);
    }
}
