<!--
To change this template, choose Tools | Templates
and open the template in the editor.
-->
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Profile Admin</title>
        <style>
            body {
                font-family: Trebuchet MS, Verdana, sans-serif;   
            }
            .DetailRow
            {
                display: none;
                text-align:center;
            }
            .summaryRow
            {
                cursor : pointer;
            }
            table tr td {
                text-align:center;
            }
        </style>
        <script type="text/javascript" src="http://ajax.aspnetcdn.com/ajax/jQuery/jquery-1.10.2.min.js"></script>
        <script type="text/javascript">
            $(document).ready(function() {
                $(".summaryRow").on("click", function(event) {
                    $(this).next(".DetailRow").toggle();
                    event.preventDefault();
                });
            });
        </script>
    </head>
    <body>
        <form method="POST" action="">
            <div>
                <p>
                    <label for="ipAddress">IP address</label><input type="text" name ="ipAddress" id="ipAddress" value="127.0.0.1"/>

                </p>
                <p>
                    <label for="port">Port</label><input type="text" id="port" name="port" value="27017"/>               
                </p>
                <p>
                    <label for="dbName">Database Name</label> <input type="text" name="dbName"/>
                </p>
                <p>

                    <input type="submit" value="Submit" name="btnSubmit"/>
                </p>

            </div>
        </form>
        <?php
        // put your code here
        if (isset($_POST['btnSubmit'])) {
            $mongoConn = $_POST['ipAddress'] . ':' . $_POST['port'];
            $dbName = $_POST['dbName'];
            $mongoConn = new MongoClient($mongoConn);
            $db = $mongoConn->$dbName;
            $profileCollection = new MongoCollection($db, 'system.profile');
            $counter = 0;
            echo '<table style="width:100%"><thead><tr><th>S.No</th><th>Time</th><th>Operation</th><th>Collection</th><th>Date</th></tr></thead>';
            foreach ($profileCollection->find()->sort(array('millis' => -1)) as $p) {
                $counter++;
                echo '<tr class="summaryRow">';
                echo '<td>' . $counter . '</td>';
                echo '<td>' . $p['millis'] . '</td>';
                echo '<td>' . $p['op'] . '</td>';
                echo '<td>' . $p['ns'] . '</td>';
                echo'<td>' . date("d-M-y", $p['ts']->sec) . '</td>';
                echo '</tr>';
                echo '<tr class="DetailRow">';
                echo '<td colspan=5>';
                if (array_key_exists('nscanned', $p)) {
                    echo 'Document Scanned : ' . $p['nscanned'] . ' and total returned ' . $p['nreturned'] . '<br>';
                }
                if (array_key_exists('moved', $p)) {
                    echo 'Document moved : ' . $p['moved'] . ' and total doc moved :' . $p['nmoved'] . '<br>';
                }
                if (array_key_exists('updateobj', $p)) {
                    echo '<p> ' . var_dump($p['updateobj']) . '</p>';
                }
                if (array_key_exists('query', $p)) {
                    echo '<p style="font-size:12px">';
                    var_dump($p['query']);
                    echo '</p>';
                }
                echo '</td></tr>';
            }
            echo '</table>';
            if ($counter == 0)
                echo ' No records found !';
        }
        ?>

    </body>
</html>
