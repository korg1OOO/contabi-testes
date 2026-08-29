<?php
namespace app\repositories;

use app\database\ConnectionFactory;
use PDO;

class DespachoRpiRepository
{
    private PDO $conn;

    public function __construct()
    {
        $this->conn = ConnectionFactory::getConnection();
    }

    public function buscarCarteirasPorProcesso(string $numeroProcesso): array
    {
        $numeroProcesso = preg_replace('/\D/', '', $numeroProcesso);
        $sql = "SELECT c.usuario_id, m.id marca_id, NULL patente_id FROM marcas m INNER JOIN clientes c ON m.cliente_id = c.id WHERE REPLACE(REPLACE(REPLACE(m.numero_processo, '.', ''), '-', ''), '/', '') = :numero
                UNION ALL
                SELECT c.usuario_id, NULL marca_id, p.id patente_id FROM patentes p INNER JOIN clientes c ON p.cliente_id = c.id WHERE REPLACE(REPLACE(REPLACE(p.numero_processo, '.', ''), '-', ''), '/', '') = :numero";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['numero' => $numeroProcesso]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function existe(array $dados): bool
    {
        $stmt = $this->conn->prepare('SELECT COUNT(*) FROM despachos_rpi WHERE numero_revista = :numero_revista AND numero_processo = :numero_processo AND COALESCE(codigo_despacho, "") = :codigo_despacho');
        $stmt->execute([
            'numero_revista' => $dados['numero_revista'],
            'numero_processo' => $dados['numero_processo'],
            'codigo_despacho' => $dados['codigo_despacho'] ?? ''
        ]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function save(array $data): bool
    {
        $sql = "INSERT INTO despachos_rpi 
                (numero_revista, data_publicacao, numero_processo, codigo_despacho, descricao, marca_id, patente_id, processado)
                VALUES (:numero_revista, :data_publicacao, :numero_processo, :codigo_despacho, :descricao, :marca_id, :patente_id, :processado)";

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($data);
    }

    public function getDespachosByUsuario(int $usuarioId): array
{
    $sql = "SELECT d.*, 
                   COALESCE(m.numero_processo, p.numero_processo) as numero_processo,
                   COALESCE(m.titular, p.titular) as titular
            FROM despachos_rpi d
            LEFT JOIN marcas m ON d.marca_id = m.id
            LEFT JOIN patentes p ON d.patente_id = p.id
            LEFT JOIN clientes c ON (m.cliente_id = c.id OR p.cliente_id = c.id)
            WHERE c.usuario_id = :usuarioId
            ORDER BY d.data_publicacao DESC, d.id DESC";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute(['usuarioId' => $usuarioId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function marcarComoProcessadoDoUsuario(int $id, int $usuarioId): bool
{
    $sql = "UPDATE despachos_rpi d
            LEFT JOIN marcas m ON d.marca_id = m.id
            LEFT JOIN patentes p ON d.patente_id = p.id
            LEFT JOIN clientes c ON (m.cliente_id = c.id OR p.cliente_id = c.id)
            SET d.processado = 1
            WHERE d.id = :id AND c.usuario_id = :usuario_id";
    $stmt = $this->conn->prepare($sql);
    return $stmt->execute(['id' => $id, 'usuario_id' => $usuarioId]);
}

public function getDespachoById(int $id): array|false
{
    $stmt = $this->conn->prepare("SELECT * FROM despachos_rpi WHERE id = :id");
    $stmt->execute(['id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

public function getConnection(): \PDO
{
    return $this->conn;
}
}