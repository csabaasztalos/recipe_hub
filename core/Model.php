<?php

class Model
{
    /**
     *  @throws NotFoundException|DBException
     */

    private DBHandler $db;

    public function __construct()
    {
        $this->db = Container::Get('db');
    }

    public function GetPageData(string $page): array
    {
        $result = $this->db->RunQuery(
            "SELECT * FROM `pages` WHERE `pageID` = ?",
            [new DBParam(DBTypes::STRING, $page)]
        );

        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        } else {
            throw new NotFoundException("No results found for page $page");
        }
    }


    /**
     * @throws DBException|NotFoundException
     */
    public function LoadContent(string|null $page = null, string|null $flag = null): array
    {
        if ($page === null && $flag === null) {
            $results = $this->db->RunQuery("SELECT * FROM `contents`");
        } else {
            $results = $this->db->RunQuery(
                "SELECT * FROM `contents` WHERE `pageID` = ? AND `flag` = ?",
                [new DBParam(DBTypes::STRING, $page),
                    new DBParam(DBTypes::STRING, $flag)]
            );
        }

        if ($results->num_rows > 0 && $page !== null && $flag !== null) {
            $contentData = $results->fetch_assoc();
            return ['flag' => $flag, 'content' => $contentData['content']];
        } elseif ($results->num_rows > 0) {
            Logger::Log("LoadContent: No results found for pageID: $page, flag: $flag", logLvl::Warning);
            return $results->fetch_all(MYSQLI_ASSOC);
        } else {
            Logger::Log("No results found for page $page", logLvl::Warning);
            throw new NotFoundException("No results found for page $page");
        }
    }


    /**
     * @throws DBException
     */
    public function ModifyContent(string $content, string $page, string $flag): bool
    {
        $result = $this->db->RunQuery("UPDATE `contents` SET `content` = ? WHERE `pageID` = ? AND `flag` = ?",
            [new DBParam(DBtypes::STRING, $content),
                new DBParam(DBtypes::STRING, $page),
                new DBParam(DBtypes::STRING, $flag)]
            );

        if (count($result['page_id']) === 1) {
            return true;
        }
        return false;
    }
}

//TODO: better Exception handleing and loging