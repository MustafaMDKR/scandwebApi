<?php
namespace API\controllers;

use API\core\Router;
use API\models\ProductModel;

class ProductController
{
    public function __construct(private ProductModel $product)
    {
    }

    public function processRequest(string $method, ?string $id): void
    {
        if ($id) {
            $this->singleRequest($method, $id);
        } else {
            $this->collectionRequest($method);
        }
    }

    private function singleRequest(string $method, string $id): void
    {
        $product = $this->product->findById($id);
        echo json_encode($product);
    }

    private function collectionRequest(string $method): void
    {
        switch ($method) {
            case 'GET':
                echo json_encode($this->product->getAllProducts());

                break;

            case 'POST':
                $data = (array) json_decode(file_get_contents('php://input'), true);
                $errors = $this->validateErrors($data);
                if (!empty($errors)) {
                    http_response_code(422);
                    echo json_encode([
                        'errors' => $errors,
                    ]);

                    break;
                }
                $id = $this->product->createProduct($data);
                http_response_code(201);
                echo json_encode([
                    'message' => 'Product is created successfully',
                    'id' => $id,
                ]);
                break;
            
            case 'DELETE':
                $data = (array) json_decode(file_get_contents('php://input'), true);
                $deleted = [];
                foreach ($data as $value) {
                    $product = $this->product->findById($value);
                    if(!$product) {
                        $result["errors"] = "Product with id: $value does not exist";
                    } else {
                        $this->product->deleteById($value);
                        $result["deleted"] = array_push($deleted, $value);
                        $result["message"] = "products are deleted successfully";
                    }
                }
                echo json_encode($deleted);
                break;

            default:
                http_response_code(405);
                header("Allow: DELETE,GET,POST");
                break;
        }
    }

    private function validateErrors(array $data): array
    {
        $errors = [];
        $keys = array_keys($data);
        $attrs = [
            'name','sku','price','size','type','weight','width','height','length'
        ];
        if ((!empty($keys)) || (!empty($data))) {
            foreach ($attrs as $attr) {
                foreach ($keys as $key) {
                    if (!in_array($attr, $keys)) {
                        $errors[$attr] = ucfirst($attr). " is required";
                    } else if ($key === 'sku') {
                        $item = $this->product->findOne($data[$key]);
                        if ($item) {
                            if (($item['sku'] === $data['sku'])) {
                                $errors['sku'] = "SKU is already exists";
                            }
                        }
                    } else if ($key === 'type' && $data['type'] === 'DVD') {
                        if (($data['size'] <= 0) || (!is_int($data['size']))) {
                            $errors['size'] = "Size value is invalid";
                        }
                    } else if ($key === 'type' && $data['type'] === 'Book') {
                        if (($data['weight'] <= 0) || (!is_numeric($data['weight']))) {
                            $errors['weight'] = "Weight value is invalid";
                        }
                    } else if ($key === 'type' && $data['type'] === 'Furniture') {
                        if (($data['width'] <= 0) || (!is_numeric($data['width']))) {
                            $errors['width'] = "width value is invalid";
                        }
                        if (($data['height'] <= 0) || (!is_numeric($data['height']))) {
                            $errors['height'] = "height value is invalid";
                        }
                        if (($data['length'] <= 0) || (!is_numeric($data['length']))) {
                            $errors['length'] = "length value is invalid";
                        }
                    } else if ($key === 'type' && ($data['type'] != 'Furniture'||'DVD'||'Book')) {
                        $errors['type'] = "Type value is invalid";
                    }
                }
            }
        } else {
            $errors['Request'] = 'Invalid empty request body';
        }

        return $errors;
    }
}
