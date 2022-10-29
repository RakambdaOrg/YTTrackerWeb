<?php
    require_once(__DIR__ . '/api/v2/model/DBConnection.class.php');

    if(true)
    {
        error_reporting(E_ALL);
        ini_set('display_errors', '1');
    }

    $dev = isset($_GET['dev']);
    $conn = YTT\DBConnection::getConnection();

    $prepared = $conn->prepare("SELECT UserId, Type, StatDay FROM YTT_Records GROUP BY UserId, Type, StatDay HAVING COUNT(*) > 1");
    $prepared->execute(array());
    $result = $prepared->fetchAll();
    foreach($result as $key => $row)
    {
        concatenate($conn, $row['UserId'], $row['Type'], $row['StatDay']);
    }

    //1_542_580
    //1_510_835

    /**
     * @param PDO $conn
     * @param $userId
     * @param $type
     * @param $date
     */
    function concatenate($conn, $userId, $type, $date)
    {
        if(!$conn->beginTransaction())
        {
            echo "Failed to begin transaction <br/>\n";
            return;
        }

        $prepared = $conn->prepare("SELECT SUM(Stat) AS S, SUM(Amount) AS A FROM `YTT_Records` WHERE UserId=:userid AND Type=:type AND StatDay=DATE(:date)");
        $prepared->execute(array(':userid' => $userId, ':type' => $type, ':date' => $date));
        if($row = $prepared->fetch())
        {
            $s = $row['S'];
            $a = $row['A'];

            $prepared2 = $conn->prepare("DELETE FROM `YTT_Records` WHERE UserId=:userid AND Type=:type AND StatDay=DATE(:date)");
            if($prepared2->execute(array(':userid' => $userId, ':type' => $type, ':date' => $date)))
            {
                $prepared3 = $conn->prepare("INSERT INTO YTT_Records(UserId, Type, Stat, StatDay, Amount) VALUES (:userid, :type, :stat, DATE(:date), :amount)");
                if($prepared3->execute(array(':userid' => $userId, ':type' => $type, ':date' => $date, ':stat' => $s, ':amount' => $a)))
                {
                    $conn->commit();
                }
                else
                {
                    print_r($prepared3->errorInfo());
                    echo "<br/>\n";
                    $conn->rollBack();
                }
            }
            else
            {
                print_r($prepared2->errorInfo());
                echo "<br/>\n";
                $conn->rollBack();
            }
        }
        else
        {
            print_r($prepared->errorInfo());
            echo "<br/>\n";
            $conn->rollBack();
        }
    }