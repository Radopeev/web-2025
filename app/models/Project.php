<?php

// Assume $conn is globally available and a mysqli instance
// For production, consider dependency injection instead of global $conn

class Project {
    /**
     * Retrieves all projects from the database.
     * This method fetches all projects without pagination or search.
     *
     * @return array An array of project records, each as an associative array.
     */
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
    /**
     * Retrieves a paginated list of projects, with optional search capabilities.
     * (Existing method, included for context)
     */
    public static function getPaginatedProjects(string $searchQuery = '', int $limit = 10, int $offset = 0): array
    {
        global $conn;

        if (!$conn instanceof mysqli) {
            error_log("MySQLi connection not available in Project::getPaginatedProjects.");
            return [];
        }

        try {
            $sql = "SELECT id, user_id, title, description, config_file, created_at
                    FROM projects";
            $params = [];
            $types = '';

            // Add search conditions if a search query is provided
            if (!empty($searchQuery)) {
                $sql .= " WHERE title LIKE ? OR description LIKE ?";
                $searchTerm = '%' . $searchQuery . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $types .= 'ss'; // 's' for string, two 's' for two parameters
            }

            $sql .= " ORDER BY created_at DESC LIMIT ? OFFSET ?"; // Add LIMIT and OFFSET for pagination

            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                error_log("Error preparing statement in getPaginatedProjects: " . $conn->error);
                return [];
            }

            // Bind parameters dynamically using call_user_func_array
            $params[] = $limit;
            $params[] = $offset;
            $types .= 'ii';

            $bindArgs = [];
            $bindArgs[] = &$types;
            for ($i = 0; $i < count($params); $i++) {
                $bindArgs[] = &$params[$i];
            }

            call_user_func_array([$stmt, 'bind_param'], $bindArgs);

            $stmt->execute();
            $result = $stmt->get_result();

            return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        } catch (Exception $e) {
            error_log("Error fetching paginated projects: " . $e->getMessage());
            return [];
        } finally {
            if (isset($stmt) && $stmt) {
                $stmt->close();
            }
        }
    }

    /**
     * Gets the total count of projects, with optional search capabilities.
     * (Existing method, included for context)
     */
    public static function getTotalProjectCount(string $searchQuery = ''): int
    {
        global $conn;

        if (!$conn instanceof mysqli) {
            error_log("MySQLi connection not available in Project::getTotalProjectCount.");
            return 0;
        }

        try {
            $sql = "SELECT COUNT(*) FROM projects";
            $params = [];
            $types = '';

            // Add search conditions if a search query is provided
            if (!empty($searchQuery)) {
                $sql .= " WHERE title LIKE ? OR description LIKE ?";
                $searchTerm = '%' . $searchQuery . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $types .= 'ss';
            }

            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                error_log("Error preparing statement in getTotalProjectCount: " . $conn->error);
                return 0;
            }

            if (!empty($params)) {
                $bindArgs = [];
                $bindArgs[] = &$types;
                for ($i = 0; $i < count($params); $i++) {
                    $bindArgs[] = &$params[$i];
                }
                call_user_func_array([$stmt, 'bind_param'], $bindArgs);
            }

            $stmt->execute();
            $result = $stmt->get_result();

            return $result ? $result->fetch_row()[0] : 0;
        } catch (Exception $e) {
            error_log("Error counting projects: " . $e->getMessage());
            return 0;
        } finally {
            if (isset($stmt) && $stmt) {
                $stmt->close();
            }
        }
    }

    /**
     * Retrieves a single project's details by its ID.
     * Renamed from getProjectDetails to getById for clarity and consistency.
     */
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

    /**
     * Retrieves all source files associated with a project.
     * (Previously getProjectFiles, but renamed to be more specific to 'source' files).
     */
    public static function getProjectSourceFiles(int $projectId): array
    {
        global $conn;

        if (!$conn instanceof mysqli) {
            error_log("MySQLi connection not available in Project::getProjectSourceFiles.");
            return [];
        }

        try {
            // Assuming your 'files' table has an 'is_config' column or similar
            // to distinguish source files from config files.
            // If not, you might need a separate 'source_files' table or adjust schema.
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

    /**
     * Retrieves all instruments associated with a project.
     * (Existing method, included for context)
     */
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

    /**
     * Updates an existing project's title and description in the database.
     * Renamed from updateProject to update for broader applicability.
     *
     * @param int $projectId The ID of the project to update.
     * @param array $data An associative array of data to update (e.g., ['title' => 'New Title']).
     * @return bool True on success, false on failure.
     * @throws mysqli_sql_exception If a database error occurs.
     */
    public static function update(int $projectId, array $data): bool
    {
        global $conn;

        if (!$conn instanceof mysqli) {
            throw new Exception("Database connection not available.");
        }

        $setParts = [];
        $params = [];
        $types = '';

        // 1. Retrieve the current values for the project from the database.
        // We select all columns that might be updated to compare against new data.
        $selectSql = "SELECT title, description FROM projects WHERE id = ?";
        $selectStmt = $conn->prepare($selectSql);

        if ($selectStmt === false) {
            // Throw an exception if the SELECT statement itself cannot be prepared.
            throw new mysqli_sql_exception("Failed to prepare SELECT statement: " . $conn->error);
        }

        // Bind the project ID for the SELECT query.
        $selectStmt->bind_param('i', $projectId);
        $selectStmt->execute();
        $result = $selectStmt->get_result();
        $currentProjectData = $result->fetch_assoc(); // Fetch the current row data.
        $selectStmt->close();

        if (
            (isset($data['title']) && $data['title'] === $currentProjectData['title'])
            || (isset($data['description']) && $data['description'] && $data['description'] === $currentProjectData['description'])
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
            return false; // Nothing to update
        }

        $sql = "UPDATE projects SET " . implode(', ', $setParts) . " WHERE id = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            throw new mysqli_sql_exception("Failed to prepare statement: " . $conn->error);
        }

        $params[] = $projectId; // Add projectId to parameters
        $types .= 'i'; // Add type for projectId

        // Bind parameters dynamically using call_user_func_array
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

    /**
     * Updates an existing instrument record.
     * Changed signature to accept instrument ID and an array of data.
     *
     * @param int $instrumentId The ID of the instrument to update.
     * @param array $data An associative array of instrument data (e.g., ['name' => 'New Name']).
     * @return bool True on success, false on failure.
     * @throws mysqli_sql_exception If a database error occurs.
     */
    public static function updateInstrument(int $instrumentId, array $data): bool
    {
        global $conn;

        if (!$conn instanceof mysqli) {
            throw new Exception("Database connection not available.");
        }

        // 1. Fetch current data for comparison
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

        // If no instrument found with the given ID, we can't update it.
        // Decide how to handle this: return false, or throw an exception.
        if (!$currentInstrument) {
            // Option 1: Return false if the instrument doesn't exist
            return false;
            // Option 2: Throw an exception if an invalid instrumentId is passed
            // throw new Exception("Instrument with ID $instrumentId not found.");
        }

        $setParts = [];
        $params = [];
        $types = '';
        $hasChanges = false; // Flag to track if any actual changes are detected

        // 2. Compare incoming data with current data and build update parts
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

        // If no fields are provided in $data OR if all provided fields have the same values
        if (empty($setParts)) { // This condition now also covers the "same values" case
            return false; // Nothing to update or no actual changes detected
        }

        // If we reach here, it means there are actual changes to be made
        $sql = "UPDATE instruments SET " . implode(', ', $setParts) . " WHERE id = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            throw new mysqli_sql_exception("Failed to prepare statement: " . $conn->error);
        }

        $params[] = $instrumentId; // Add instrumentId to parameters
        $types .= 'i'; // Add type for instrumentId

        // Use call_user_func_array for bind_param with dynamic arguments
        $bindArgs = [];
        $bindArgs[] = &$types;
        for ($i = 0; $i < count($params); $i++) {
            $bindArgs[] = &$params[$i];
        }
        call_user_func_array([$stmt, 'bind_param'], $bindArgs);

        if ($stmt->execute()) {
            // Return true if affected_rows > 0 (meaning something was actually changed in the DB)
            // Note: Even if affected_rows is 0 here, it means we ran an update,
            // but the DB decided nothing changed. The `hasChanges` flag above is
            // better for preventing the query entirely if values are the same.
            return $stmt->affected_rows > 0;
        } else {
            throw new mysqli_sql_exception("Failed to execute statement: " . $stmt->error);
        }
    }
    /**
     * Adds a new instrument record to the database for a specific project.
     * Changed signature to accept an array of data.
     *
     * @param int $projectId The ID of the project to associate with.
     * @param array $data An associative array of instrument data (name, type, description, access_link).
     * @return bool True on success, false on failure.
     * @throws mysqli_sql_exception If a database error occurs.
     */
    public static function addInstrument(int $projectId, array $data): bool
    {
        global $conn;

        if (!$conn instanceof mysqli) {
            throw new Exception("Database connection not available.");
        }

        // Ensure all required fields are present in $data for insertion
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

    /**
     * Deletes multiple instrument records by their IDs.
     *
     * @param array $instrumentIds An array of instrument IDs to delete.
     * @return bool True on success, false if no rows were affected or on error.
     * @throws mysqli_sql_exception If a database error occurs.
     */
    public static function deleteInstruments(array $instrumentIds): bool
    {
        global $conn;

        if (!$conn instanceof mysqli) {
            throw new Exception("Database connection not available.");
        }

        if (empty($instrumentIds)) {
            return true; // Nothing to delete
        }

        // Create a string of placeholders for the IN clause (?, ?, ?)
        $placeholders = implode(',', array_fill(0, count($instrumentIds), '?'));
        $types = str_repeat('i', count($instrumentIds)); // All IDs are integers

        $sql = "DELETE FROM instruments WHERE id IN ($placeholders)";
        $stmt = $conn->prepare($sql);

        if ($stmt === false) {
            throw new mysqli_sql_exception("Failed to prepare statement: " . $conn->error);
        }

        // Bind parameters dynamically
        $bindArgs = [];
        $bindArgs[] = &$types;
        for ($i = 0; $i < count($instrumentIds); $i++) {
            $bindArgs[] = &$instrumentIds[$i]; // Pass each ID by reference
        }
        call_user_func_array([$stmt, 'bind_param'], $bindArgs);

        if ($stmt->execute()) {
            return $stmt->affected_rows > 0;
        } else {
            throw new mysqli_sql_exception("Failed to execute statement: " . $stmt->error);
        }
    }

    /**
     * Retrieves file paths for given source file IDs.
     * Useful before deleting files from storage.
     *
     * @param array $fileIds An array of source file IDs.
     * @return array An array of file paths.
     * @throws mysqli_sql_exception If a database error occurs.
     */
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


    /**
     * Adds a new source file record to the database for a specific project.
     *
     * @param int $projectId The ID of the project to associate with.
     * @param string $fileName The original name of the file.
     * @param string $filePath The stored path of the file.
     * @return bool True on success, false on failure.
     * @throws mysqli_sql_exception If a database error occurs.
     */
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

    /**
     * Deletes multiple source file records by their IDs.
     *
     * @param array $fileIds An array of source file IDs to delete.
     * @return bool True on success, false if no rows were affected or on error.
     * @throws mysqli_sql_exception If a database error occurs.
     * @throws Exception
     */
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
            return true; // Nothing to delete
        }

        $placeholders = implode(',', array_fill(0, count($fileIds), '?'));
        $types = str_repeat('i', count($fileIds));

        $sql = "DELETE FROM files WHERE id IN ($placeholders)"; // Ensure only source files are deleted
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

    /**
     * Deletes the configuration file path from a project record in the database.
     *
     * @param int $projectId The ID of the project.
     * @return bool True on success, false on failure.
     * @throws mysqli_sql_exception If a database error occurs.
     */
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

    /**
     * Updates the configuration file path for a project record in the database.
     *
     * @param int $projectId The ID of the project.
     * @param string $filePath The new file path for the configuration file.
     * @return bool True on success, false on failure.
     * @throws mysqli_sql_exception If a database error occurs.
     */
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

    public static function getProjectsByUserIdPaginated(int $userId, int $limit, int $offset): array
    {
        global $conn;
        if (!$conn instanceof mysqli) {
            error_log("MySQLi connection not available in Project::getProjectsByUserIdPaginated.");
            return [];
        }

        try {
            $sql = "SELECT id, title, description FROM projects WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                error_log("Error preparing statement in getProjectsByUserIdPaginated: " . $conn->error);
                return [];
            }

            $stmt->bind_param('iii', $userId, $limit, $offset);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result) {
                return $result->fetch_all(MYSQLI_ASSOC);
            } else {
                error_log("Error getting result in getProjectsByUserIdPaginated: " . $stmt->error);
                return [];
            }
        } catch (Exception $e) {
            error_log("Error fetching paginated projects: " . $e->getMessage());
            return [];
        } finally {
            if (isset($stmt) && $stmt) {
                $stmt->close();
            }
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

    public static function getProjectsByUserId($userId) {
        global $conn;
        $projects = [];
        $stmt = $conn->prepare("SELECT * FROM projects WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $projects[] = $row;
        }
        $stmt->close();
        return $projects;
    }

    public static function searchProjects($query) {
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
