<?php

class AtualizarEstoque {
    private $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function atualizarEstoque($dadosJson) {
        $dados = json_decode($dadosJson, true);
    
        if (!$dados) {
            throw new Exception("Erro ao decodificar o JSON");
        }
    
        $this->pdo->beginTransaction();
    
        try {
            foreach ($dados as $produto) {
                $produtoID = $this->getProdutoID($produto);
    
                $stmt = $this->pdo->prepare("SELECT * FROM estoque WHERE id = :id");
                $stmt->bindParam(':id', $produtoID);
                $stmt->execute();
                $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    
                if ($resultado) {
                    $stmt = $this->pdo->prepare("UPDATE estoque SET quantidade = :quantidade WHERE id = :id");
                    $stmt->bindParam(':quantidade', $produto['quantidade']);
                    $stmt->bindParam(':id', $produtoID);
                    $stmt->execute();
                } else {
                    $stmt = $this->pdo->prepare("INSERT INTO estoque (produto, cor, tamanho, deposito, data_disponibilidade, quantidade) VALUES (:produto, :cor, :tamanho, :deposito, :data_disponibilidade, :quantidade)");
                    $stmt->bindParam(':produto', $produto['produto']);
                    $stmt->bindParam(':cor', $produto['cor']);
                    $stmt->bindParam(':tamanho', $produto['tamanho']);
                    $stmt->bindParam(':deposito', $produto['deposito']);
                    $stmt->bindParam(':data_disponibilidade', $produto['data_disponibilidade']);
                    $stmt->bindParam(':quantidade', $produto['quantidade']);
                    $stmt->execute();
                }
            }
    
            $this->pdo->commit();
            echo "Estoque atualizado com sucesso!";
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
    

    private function getProdutoID($produto) {
        $stmt = $this->pdo->prepare("SELECT id FROM estoque WHERE produto = :produto AND cor = :cor AND tamanho = :tamanho AND deposito = :deposito AND data_disponibilidade = :data_disponibilidade");
        $stmt->bindParam(':produto', $produto['produto']);
        $stmt->bindParam(':cor', $produto['cor']);
        $stmt->bindParam(':tamanho', $produto['tamanho']);
        $stmt->bindParam(':deposito', $produto['deposito']);
        $stmt->bindParam(':data_disponibilidade', $produto['data_disponibilidade']);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? $result['id'] : null;
    }
}

$pdo = new PDO("mysql:host=127.0.0.1:3306;dbname=dbgeovendas", "dionatanp", "Senha123456@");
$estoqueUpdater = new AtualizarEstoque($pdo);

$dadosJson = file_get_contents('dados.json');

$estoqueUpdater->atualizarEstoque($dadosJson);
