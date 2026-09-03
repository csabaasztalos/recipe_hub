<?php
final class UserManagerView
{
    public static function DrawMainUserTable(array $users): array
    {
        $desktopHtml = "";
        $buttonsHtml = "";
        $mobileHtml = "";

        if (empty($users)) {
            return [
                "<tr><td colspan=\"6\" class=\"text-center text-muted\">Nincs megjeleníthető felhasználó</td></tr>",
                "<p>Nincs megjeleníthető felhasználó</p>"
            ];
        }

        foreach ($users as $user) {
            $statusCode = (int) $user['verified'];
            $color = "";
            switch ($statusCode) {
                case 1:
                    $color = "bg-success";
                    $status = "aktív";
                    break;
                case 0:
                    $color = "bg-secondary";
                    $status = "nem aktív";
                    break;
                default:
                    $color = "";
                    $status = "";
            }
            $buttonsHtml = "
                            <div>
                                <button class=\"btn btn-primary btn-sm\" 
                                                data-bs-toggle=\"modal\"
                                                data-bs-target=\"#editUserModal\"
                                                data-user-id=\"{$user['id']}\"
                                                data-user-name=\"{$user['username']}\"
                                                data-user-email=\"{$user['email']}\"
                                                data-user-status=\"{$statusCode}\"
                                                data-user-permission=\"{$user['permission_level']}\">
                                                Módosítás
                                </button>
                            </div>
                            <div>
                                <button class=\"btn btn-warning btn-sm\"
                                                data-bs-toggle=\"modal\"
                                                data-bs-target=\"\"
                                                data-user-id=\"{$user['id']}\"
                                                data-user-name=\"{$user['username']}\"
                                                data-user-email=\"{$user['email']}\"
                                                data-user-permission=\"{$user['permission_level']}\">
                                                Email
                                </button>
                            </div>
                            <div>
                                <form method=\"post\" style=\"display:inline;\">
                                    <button class=\"btn btn-danger btn-sm confirm-btn\" data-confirm=\"Biztosan törölni szeretnéd a felhasználót?\" name=\"delete\">Törlés</button>
                                    <input type=\"hidden\" name=\"user_id\" value=\"{$user['id']}\">
                                </form>
                            </div>
                        ";

            $desktopHtml .= "<tr>
                                <td>#".$user['id']."</td>
                                <td>".$user['username']."</td>
                                <td>".$user['email']."</td>
                                <td>".$user['created_at']."</td>
                                <td>".$user['permission_level']."</td>
                                <td> <span class=\"badge text-dark ".$color."\">".$status."</span></td><td class=\"text-end buttons\">".
                $buttonsHtml."
                            </td></tr>";

            $mobileHtml .= "
                <div class=\"card recip-card\">
                    <div class=\"card-body\">
                        <h3 class=\"card-title\">{$user['username']}</h5>
                        <p class=\"card-text\">Státusz: {$status}</p>
                        <p class=\"card-text\">Email: {$user['email']}</p>
                        <p class=\"card-text\">Jogkör: {$user['permission_level']}</p>
                        <p class=\"card-text\">Regisztráció dátuma: {$user['created_at']}</p>
                    </div>
                    <div class=\"card-body\">
                        <p>Műveletek:</p>
                        <div class=\"buttons\">{$buttonsHtml}</div>
                    </div>
                </div>
                ";
        }
        return [$desktopHtml, $mobileHtml];
    }

    public static function BuildPermissionOptions(?int $selected): string
    {
        global $cfg;
        $permissionHTML = '';
        foreach ($cfg['newUserPermissionOptions'] as $level => $name) {
            $level = (int) $level;
            $select = "";

            if ($selected === $level) {
                $select = "selected";
            }

            match ($level) {
                1 => $name = "felhasználó",
                2 => $name = "admin",
            };
            $permissionHTML .= "<option value = \"{$level}\" $select>{$name}</option>";
        }
        return $permissionHTML;
    }

    public static function BuildStatusOptions(): string
    {
        return "<option value = \"1\">Aktív</option><option value = \"0\">Nem aktív</option>";
    }
}