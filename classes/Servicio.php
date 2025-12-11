<?php

class Servicio
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function obtenerPorSlugs(array $slugs)
    {
        if (empty($slugs)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($slugs), '?'));

        $sql = "SELECT id, nombre, descripcion, precio, tiempo_estimado, slug 
                FROM servicios 
                WHERE slug IN ($placeholders) AND activo = 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($slugs);
        $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $porSlug = [];
        foreach ($filas as $fila) {
            $porSlug[$fila['slug']] = $fila;
        }

        return $porSlug;
    }

    public function obtenerPorId($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM servicios WHERE id = ? AND activo = 1");
        $stmt->execute([(int)$id]);
        $servicio = $stmt->fetch(PDO::FETCH_ASSOC);

        return $servicio ?: null;
    }
}
