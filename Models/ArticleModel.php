<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Session;
use PDO;

class ArticleModel extends Model {

    public function create(string $title, string $abstract, string $filePath, int $authorId): bool {
        $sql = "INSERT INTO articles (title, abstract, file_path, id_author_user) 
            VALUES (?, ?, ?, ?)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([$title, $abstract, $filePath, $authorId]);
    }

    public function getArticlesByAuthorId(int $authorId): array {
        $stmt = $this->db->prepare("SELECT * FROM articles WHERE id_author_user = ? ORDER BY created_at DESC");
        $stmt->execute([$authorId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}