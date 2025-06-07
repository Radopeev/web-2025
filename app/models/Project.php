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

    public static function searchProjects(string $query): array {
        global $conn;

        if (!$conn instanceof mysqli) {
            error_log("MySQLi connection not available in Project::searchProjects.");
            return [];
        }

        if (empty($query)) {
            return self::getAllProjects();
        }

        try {
            $sql = "SELECT id, user_id, title, description, config_file, created_at
                    FROM projects
                    WHERE title LIKE ? OR description LIKE ?
                    ORDER BY created_at DESC";

            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                error_log("Error preparing statement in searchProjects: " . $conn->error);
                return [];
            }

            $searchTerm = '%' . $query . '%';
            $stmt->bind_param('ss', $searchTerm, $searchTerm);

            $stmt->execute();

            $result = $stmt->get_result();

            if ($result) {
                return $result->fetch_all(MYSQLI_ASSOC);
            } else {
                error_log("Error getting result in searchProjects: " . $stmt->error);
                return [];
            }
        } catch (Exception $e) {
            error_log("Error searching projects: " . $e->getMessage());
            return [];
        } finally {
            if (isset($stmt) && $stmt) {
                $stmt->close();
            }
        }
    }

    /**
     * Retrieves a single project by its ID from the database.
     *
     * @param int $id The ID of the project to retrieve.
     * @return array|null The project data if found, otherwise null.
     */
    public static function getProjectById(int $id): ?array {
        // Access the global mysqli connection variable
        global $conn;

        // Ensure the connection is valid before proceeding
        if (!$conn instanceof mysqli) {
            error_log("MySQLi connection not available in Project::getProjectById.");
            return null;
        }

        try {
            $sql = "SELECT id, user_id, title, description, config_file, created_at FROM projects WHERE id = ? LIMIT 1";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                error_log("Error preparing statement in getProjectById: " . $conn->error);
                return null;
            }

            $stmt->bind_param('i', $id); // 'i' indicates an integer parameter
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result) {
                $project = $result->fetch_assoc(); // Fetch a single row as an associative array
                return $project ?: null; // Return null if no project found
            } else {
                error_log("Error getting result in getProjectById: " . $stmt->error);
                return null;
            }
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
     * Adds a new project to the database.
     *
     * @param int $userId The ID of the user owning the project.
     * @param string $title The title of the project.
     * @param string $description The description of the project.
     * @param string|null $configFile The path to the configuration file (optional).
     * @return int The ID of the newly inserted project, or 0 on failure.
     */
    public static function addProject(int $userId, string $title, string $description, ?string $configFile = null): int {
        global $conn;
        if (!$conn instanceof mysqli) {
            error_log("MySQLi connection not available in Project::addProject.");
            return 0;
        }

        try {
            $sql = "INSERT INTO projects (user_id, title, description, config_file) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                error_log("Error preparing statement in addProject: " . $conn->error);
                return 0;
            }

            // 'isss' indicates integer, string, string, string parameters
            $stmt->bind_param('isss', $userId, $title, $description, $configFile);

            if ($stmt->execute()) {
                return $conn->insert_id; // Get the ID of the newly inserted row
            } else {
                error_log("Error executing statement in addProject: " . $stmt->error);
                return 0;
            }
        } catch (Exception $e) {
            error_log("Error adding project: " . $e->getMessage());
            return 0;
        } finally {
            if (isset($stmt) && $stmt) {
                $stmt->close();
            }
        }
    }

    /**
     * Updates an existing project in the database.
     *
     * @param int $id The ID of the project to update.
     * @param string $title The new title of the project.
     * @param string $description The new description of the project.
     * @param string|null $configFile The new path to the configuration file (optional).
     * @return bool True on success, false on failure.
     */
    public static function updateProject(int $id, string $title, string $description, ?string $configFile = null): bool {
        global $conn;
        if (!$conn instanceof mysqli) {
            error_log("MySQLi connection not available in Project::updateProject.");
            return false;
        }

        try {
            $sql = "UPDATE projects SET title = ?, description = ?, config_file = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                error_log("Error preparing statement in updateProject: " . $conn->error);
                return false;
            }

            // 'sssi' indicates string, string, string, integer parameters
            $stmt->bind_param('sssi', $title, $description, $configFile, $id);

            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error updating project: " . $e->getMessage());
            return false;
        } finally {
            if (isset($stmt) && $stmt) {
                $stmt->close();
            }
        }
    }

    /**
     * Deletes a project from the database.
     *
     * @param int $id The ID of the project to delete.
     * @return bool True on success, false on failure.
     */
    public static function deleteProject(int $id): bool {
        global $conn;
        if (!$conn instanceof mysqli) {
            error_log("MySQLi connection not available in Project::deleteProject.");
            return false;
        }

        try {
            $sql = "DELETE FROM projects WHERE id = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                error_log("Error preparing statement in deleteProject: " . $conn->error);
                return false;
            }

            $stmt->bind_param('i', $id); // 'i' indicates an integer parameter
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("Error deleting project: " . $e->getMessage());
            return false;
        } finally {
            if (isset($stmt) && $stmt) {
                $stmt->close();
            }
        }
    }

    /**
     * Retrieves projects owned by a specific user.
     *
     * @param int $userId The ID of the user.
     * @return array An array of projects owned by the user.
     */
    public static function getProjectsByUserId(int $userId): array {
        global $conn;
        if (!$conn instanceof mysqli) {
            error_log("MySQLi connection not available in Project::getProjectsByUserId.");
            return [];
        }

        try {
            $sql = "SELECT id, user_id, title, description, config_file, created_at FROM projects WHERE user_id = ? ORDER BY created_at DESC";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                error_log("Error preparing statement in getProjectsByUserId: " . $conn->error);
                return [];
            }

            $stmt->bind_param('i', $userId); // 'i' indicates an integer parameter
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result) {
                return $result->fetch_all(MYSQLI_ASSOC);
            } else {
                error_log("Error getting result in getProjectsByUserId: " . $stmt->error);
                return [];
            }
        } catch (Exception $e) {
            error_log("Error fetching user projects: " . $e->getMessage());
            return [];
        } finally {
            if (isset($stmt) && $stmt) {
                $stmt->close();
            }
        }
    }

    public static function getProjectsByUserIdPaginated(int $userId, int $limit, int $offset): array {
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

    public static function countProjectsByUserId(int $userId): int {
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
}
