<?php
class Project {
    public static function getAllProjects(): array {
        global $conn;

        if (!$conn instanceof mysqli) {
            error_log("MySQLi connection not available in Project::getAllProjects.");
            return [];
        }

        try {
            $result = $conn->query("SELECT id, user_id, title, description, config_file, created_at FROM projects ORDER BY created_at DESC");

            if ($result) {
                return $result->fetch_all(MYSQLI_ASSOC);
            } else {
                error_log("Error executing query in getAllProjects: " . $conn->error);
                return [];
            }
        } catch (Exception $e) {
            error_log("Error fetching all projects: " . $e->getMessage());
            return [];
        }
    }

    public static function getAllProjectsForUser($userId): array {
        global $conn;

        $projects = [];

        $stmt = $conn->prepare("SELECT * FROM projects WHERE user_id = ?");
        if ($stmt === false) {
            return $projects;
        }

        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $projects[] = $row;
        }

        $stmt->close();

        return $projects;
    }

    public static function getById(int $projectId): ?array
    {
        global $conn;

        if (!$conn instanceof mysqli) {
            error_log("MySQLi connection not available in Project::getById.");
            return null;
        }

        try {
            $stmt = $conn->prepare("SELECT id, user_id, title, description, config_file, created_at FROM projects WHERE id = ?");
            if (!$stmt) {
                error_log("Error preparing statement in getById: " . $conn->error);
                return null;
            }
            $stmt->bind_param('i', $projectId);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result ? $result->fetch_assoc() : null;
        } catch (Exception $e) {
            error_log("Error fetching project by ID: " . $e->getMessage());
            return null;
        } finally {
            if (isset($stmt) && $stmt) {
                $stmt->close();
            }
        }
    }

    public static function getProjectSourceFiles(int $projectId): array
    {
        global $conn;

        if (!$conn instanceof mysqli) {
            error_log("MySQLi connection not available in Project::getProjectSourceFiles.");
            return [];
        }

        try {
            $stmt = $conn->prepare("SELECT id, project_id, original_name, file_path, uploaded_at FROM files WHERE project_id = ? ORDER BY uploaded_at DESC");
            if (!$stmt) {
                error_log("Error preparing statement in getProjectSourceFiles: " . $conn->error);
                return [];
            }
            $stmt->bind_param('i', $projectId);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        } catch (Exception $e) {
            error_log("Error fetching project source files: " . $e->getMessage());
            return [];
        } finally {
            if (isset($stmt) && $stmt) {
                $stmt->close();
            }
        }
    }

    public static function getProjectInstruments(int $projectId): array
    {
        global $conn;

        if (!$conn instanceof mysqli) {
            error_log("MySQLi connection not available in Project::getProjectInstruments.");
            return [];
        }

        try {
            $stmt = $conn->prepare("SELECT id, project_id, name, type, description, access_link FROM instruments WHERE project_id = ? ORDER BY name ASC");
            if (!$stmt) {
                error_log("Error preparing statement in getProjectInstruments: " . $conn->error);
                return [];
            }
            $stmt->bind_param('i', $projectId);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        } catch (Exception $e) {
            error_log("Error fetching project instruments: " . $e->getMessage());
            return [];
        } finally {
            if (isset($stmt) && $stmt) {
                $stmt->close();
            }
        }
    }

    public static function update(int $projectId, array $data): bool
    {
        global $conn;

        if (!$conn instanceof mysqli) {
            throw new Exception("Database connection not available.");
        }

        $setParts = [];
        $params = [];
        $types = '';

        $selectSql = "SELECT title, description FROM projects WHERE id = ?";
        $selectStmt = $conn->prepare($selectSql);

        if ($selectStmt === false) {
            throw new mysqli_sql_exception("Failed to prepare SELECT statement: " . $conn->error);
        }

        $selectStmt->bind_param('i', $projectId);
        $selectStmt->execute();
        $result = $selectStmt->get_result();
        $currentProjectData = $result->fetch_assoc();
        $selectStmt->close();

        if (
            (!isset($data['title']) || $data['title'] === $currentProjectData['title']) &&
            (!isset($data['description']) || $data['description'] === $currentProjectData['description'])
        ) {
            return true;
        }

        if (isset($data['title'])) {
            $setParts[] = "title = ?";
            $params[] = $data['title'];
            $types .= 's';
        }
        if (isset($data['description'])) {
            $setParts[] = "description = ?";
            $params[] = $data['description'];
            $types .= 's';
        }

        if (empty($setParts)) {
            return false;
        }

        $sql = "UPDATE projects SET " . implode(', ', $setParts) . " WHERE id = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            throw new mysqli_sql_exception("Failed to prepare statement: " . $conn->error);
        }

        $params[] = $projectId;
        $types .= 'i';

        $bindArgs = [];
        $bindArgs[] = &$types;
        for ($i = 0; $i < count($params); $i++) {
            $bindArgs[] = &$params[$i];
        }
        call_user_func_array([$stmt, 'bind_param'], $bindArgs);

        if ($stmt->execute()) {
            return $stmt->affected_rows > 0;
        } else {
            throw new mysqli_sql_exception("Failed to execute statement: " . $stmt->error);
        }
    }

    public static function updateInstrument(int $instrumentId, array $data): bool
    {
        global $conn;

        if (!$conn instanceof mysqli) {
            throw new Exception("Database connection not available.");
        }

        $currentDataSql = "SELECT name, type, description, access_link FROM instruments WHERE id = ?";
        $stmtCurrent = $conn->prepare($currentDataSql);

        if ($stmtCurrent === false) {
            throw new mysqli_sql_exception("Failed to prepare current data fetch statement: " . $conn->error);
        }

        $stmtCurrent->bind_param('i', $instrumentId);
        $stmtCurrent->execute();
        $result = $stmtCurrent->get_result();
        $currentInstrument = $result->fetch_assoc();
        $stmtCurrent->close();

        if (!$currentInstrument) {
            return false;
        }

        $setParts = [];
        $params = [];
        $types = '';
        $hasChanges = false;

        if (isset($data['name']) && $data['name'] !== $currentInstrument['name']) {
            $setParts[] = "name = ?";
            $params[] = $data['name'];
            $types .= 's';
            $hasChanges = true;
        }
        if (isset($data['type']) && $data['type'] !== $currentInstrument['type']) {
            $setParts[] = "type = ?";
            $params[] = $data['type'];
            $types .= 's';
            $hasChanges = true;
        }
        if (isset($data['description']) && $data['description'] !== $currentInstrument['description']) {
            $setParts[] = "description = ?";
            $params[] = $data['description'];
            $types .= 's';
            $hasChanges = true;
        }
        if (isset($data['access_link']) && $data['access_link'] !== $currentInstrument['access_link']) {
            $setParts[] = "access_link = ?";
            $params[] = $data['access_link'];
            $types .= 's';
            $hasChanges = true;
        }

        if(!$hasChanges) {
            return true;
        }

        if (empty($setParts)) {
            return false;
        }

        $sql = "UPDATE instruments SET " . implode(', ', $setParts) . " WHERE id = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            throw new mysqli_sql_exception("Failed to prepare statement: " . $conn->error);
        }

        $params[] = $instrumentId;
        $types .= 'i';

        $bindArgs = [];
        $bindArgs[] = &$types;
        for ($i = 0; $i < count($params); $i++) {
            $bindArgs[] = &$params[$i];
        }
        call_user_func_array([$stmt, 'bind_param'], $bindArgs);

        if ($stmt->execute()) {
            return $stmt->affected_rows > 0;
        } else {
            throw new mysqli_sql_exception("Failed to execute statement: " . $stmt->error);
        }
    }

    public static function addInstrument(int $projectId, array $data): bool
    {
        global $conn;

        if (!$conn instanceof mysqli) {
            throw new Exception("Database connection not available.");
        }

        $requiredFields = ['name', 'type', 'description', 'access_link'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                throw new InvalidArgumentException("Missing required instrument field: " . $field);
            }
        }

        $sql = "INSERT INTO instruments (project_id, name, type, description, access_link) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            throw new mysqli_sql_exception("Failed to prepare statement: " . $conn->error);
        }

        $stmt->bind_param(
            "issss",
            $projectId,
            $data['name'],
            $data['type'],
            $data['description'],
            $data['access_link']
        );

        if ($stmt->execute()) {
            return $stmt->affected_rows > 0;
        } else {
            throw new mysqli_sql_exception("Failed to execute statement: " . $stmt->error);
        }
    }

    public static function deleteInstruments(array $instrumentIds): bool
    {
        global $conn;

        if (!$conn instanceof mysqli) {
            throw new Exception("Database connection not available.");
        }

        if (empty($instrumentIds)) {
            return true;
        }

        $placeholders = implode(',', array_fill(0, count($instrumentIds), '?'));
        $types = str_repeat('i', count($instrumentIds));

        $sql = "DELETE FROM instruments WHERE id IN ($placeholders)";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            throw new mysqli_sql_exception("Failed to prepare statement: " . $conn->error);
        }

        $bindArgs = [];
        $bindArgs[] = &$types;
        for ($i = 0; $i < count($instrumentIds); $i++) {
            $bindArgs[] = &$instrumentIds[$i];
        }
        call_user_func_array([$stmt, 'bind_param'], $bindArgs);

        if ($stmt->execute()) {
            return $stmt->affected_rows > 0;
        } else {
            throw new mysqli_sql_exception("Failed to execute statement: " . $stmt->error);
        }
    }

    public static function getSourceFilePathsByIds(array $fileIds): array
    {
        global $conn;

        if (!$conn instanceof mysqli) {
            error_log("MySQLi connection not available in Project::getSourceFilePathsByIds.");
            return [];
        }

        if (empty($fileIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($fileIds), '?'));
        $types = str_repeat('i', count($fileIds));

        try {
            $sql = "SELECT file_path FROM files WHERE id IN ($placeholders)";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new mysqli_sql_exception("Failed to prepare statement: " . $conn->error);
            }

            $bindArgs = [];
            $bindArgs[] = &$types;
            for ($i = 0; $i < count($fileIds); $i++) {
                $bindArgs[] = &$fileIds[$i];
            }
            call_user_func_array([$stmt, 'bind_param'], $bindArgs);

            $stmt->execute();
            $result = $stmt->get_result();

            $paths = [];
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $paths[] = $row['file_path'];
                }
            }
            return $paths;
        } catch (Exception $e) {
            error_log("Error getting source file paths by IDs: " . $e->getMessage());
            return [];
        } finally {
            if (isset($stmt) && $stmt) {
                $stmt->close();
            }
        }
    }

    public static function addSourceFile(int $projectId, string $fileName, string $filePath): bool
    {
        global $conn;

        if (!$conn instanceof mysqli) {
            throw new Exception("Database connection not available.");
        }

        $sql = "INSERT INTO files (project_id, original_name, file_path) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            throw new mysqli_sql_exception("Failed to prepare statement: " . $conn->error);
        }

        $stmt->bind_param("iss", $projectId, $fileName, $filePath);

        if ($stmt->execute()) {
            return $stmt->affected_rows > 0;
        } else {
            throw new mysqli_sql_exception("Failed to execute statement: " . $stmt->error);
        }
    }

    public static function deleteSourceFiles(array $fileIds): bool
    {
        global $conn;

        foreach ($fileIds as $fileId) {
            error_log($fileId);
        }
        if (!$conn instanceof mysqli) {
            throw new Exception("Database connection not available.");
        }

        if (empty($fileIds)) {
            return true;
        }

        $placeholders = implode(',', array_fill(0, count($fileIds), '?'));
        $types = str_repeat('i', count($fileIds));

        $sql = "DELETE FROM files WHERE id IN ($placeholders)";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            throw new mysqli_sql_exception("Failed to prepare statement: " . $conn->error);
        }

        $bindArgs = [];
        $bindArgs[] = &$types;
        for ($i = 0; $i < count($fileIds); $i++) {
            $bindArgs[] = &$fileIds[$i];
        }
        call_user_func_array([$stmt, 'bind_param'], $bindArgs);

        if ($stmt->execute()) {
            return $stmt->affected_rows > 0;
        } else {
            throw new mysqli_sql_exception("Failed to execute statement: " . $stmt->error);
        }
    }

    public static function deleteConfigFile(int $projectId): bool
    {
        global $conn;

        if (!$conn instanceof mysqli) {
            throw new Exception("Database connection not available.");
        }

        $sql = "UPDATE projects SET config_file = NULL WHERE id = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            throw new mysqli_sql_exception("Failed to prepare statement: " . $conn->error);
        }

        $stmt->bind_param("i", $projectId);

        if ($stmt->execute()) {
            return $stmt->affected_rows > 0;
        } else {
            throw new mysqli_sql_exception("Failed to execute statement: " . $stmt->error);
        }
    }

    public static function updateConfigFile(int $projectId, string $filePath): bool
    {
        global $conn;

        if (!$conn instanceof mysqli) {
            throw new Exception("Database connection not available.");
        }

        $sql = "UPDATE projects SET config_file = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            throw new mysqli_sql_exception("Failed to prepare statement: " . $conn->error);
        }

        $stmt->bind_param("si", $filePath, $projectId);

        if ($stmt->execute()) {
            return $stmt->affected_rows > 0;
        } else {
            throw new mysqli_sql_exception("Failed to execute statement: " . $stmt->error);
        }
    }


    public static function countProjectsByUserId(int $userId): int
    {
        global $conn;
        if (!$conn instanceof mysqli) {
            error_log("MySQLi connection not available in Project::countProjectsByUserId.");
            return 0;
        }

        try {
            $sql = "SELECT COUNT(*) as total FROM projects WHERE user_id = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                error_log("Error preparing statement in countProjectsByUserId: " . $conn->error);
                return 0;
            }

            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result) {
                $row = $result->fetch_assoc();
                return (int) $row['total'];
            } else {
                error_log("Error getting result in countProjectsByUserId: " . $stmt->error);
                return 0;
            }
        } catch (Exception $e) {
            error_log("Error counting projects: " . $e->getMessage());
            return 0;
        } finally {
            if (isset($stmt) && $stmt) {
                $stmt->close();
            }
        }
    }

    public static function searchProjects($query): array
    {
        global $conn;
        $projects = [];
        $like = '%' . $query . '%';
        $stmt = $conn->prepare("SELECT * FROM projects WHERE title LIKE ? OR description LIKE ? ORDER BY created_at DESC");
        $stmt->bind_param("ss", $like, $like);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $projects[] = $row;
        }
        $stmt->close();
        return $projects;
    }
}
