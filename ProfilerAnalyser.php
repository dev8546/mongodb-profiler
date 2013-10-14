<?php
            if(isset($_GET['type']) && strcasecmp($_GET['type'], 'idx') == 0)
            {
                $dbName = $_GET['dbName'];
                $mongoConn = $_GET['ipAddress'] . ':' . $_GET['port'];
                $ns = $_GET['ns'];
                $mongoConn = new MongoClient($mongoConn);
                $db = $mongoConn->$dbName;
                $indexesCollection = new MongoCollection($db, 'system.indexes');
                $query = array('ns' => $ns);
                $result = $indexesCollection->find($query);
                $nsindexes = array();
                foreach ($result as $doc) {
                    $nsindexes[] = $doc;
                }
                echo json_encode($nsindexes);
                exit;
            }
?>
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
        $(document).ready(function () {
        $(".summaryRow").on("click", function (event) {
        var detailRow = $(this).next(".DetailRow");
        var dbName = $("#dbName").val();
        var port = $("#port").val();
        var ipAddress = $("#ipAddress").val();
        var ns = $(this).find('td:eq(3)').text();
        $.ajax({
            url: 'ProfilerAnalyser.php',
            type: 'GET',
            data: {
                type: 'idx',
                dbName: dbName,
                port: port,
                ipAddress: ipAddress,
                ns: ns
            },
            dataType: 'json',
            success: function (result) {
                var idxDetail = '';
                $.each(result, function (counter, item) {
                    idxDetail += ' Key Name : ' + item.name;
                    idxDetail += ' V  : ' + item.v;
                    idxDetail += ' col  : ';
                    $.each(item.key, function (key, value) {
                        idxDetail += key + ' : ' + value;
                    });
                });

                detailRow.toggle();
                alert(idxDetail);
                event.preventDefault();
            },
            error: function (error) {
                alert('Some error' + error);
            }
        });
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
                    <label for="dbName">Database Name</label> 
                    <?php
                    if (isset($_POST['dbName'])) {
                        echo '<input type="text" name="dbName" id="dbName" value="' . $_POST['dbName'] . '"/>';
                    } else {
                        echo '<input type="text" name="dbName" id="dbName" value=""/>';
                    }
                    ?>

                </p>
                <p>

                    <input type="submit" value="Submit" name="btnSubmit"/>
                </p>
                 <input type="hidden" name="currentsortOrder" id="currentsortOrder" value="<?php echo(isset($_POST['currentsortOrder']) ? (($_POST['currentsortOrder'] == 'asc') ? 'desc' : 'asc') :  'asc') ?>"/>
            </div>

            <?php
            if (isset($_POST['btnSubmit']) || isset($_POST['btnSort'])) {
                $mongoConn = $_POST['ipAddress'] . ':' . $_POST['port'];
                $dbName = $_POST['dbName'];
                $mongoConn = new MongoClient($mongoConn);
                $db = $mongoConn->$dbName;
                $profileCollection = new MongoCollection($db, 'system.profile');
                $counter = 0;
                echo '<div>';
                echo '<select id="sortBy" name="sortBy">';
                echo '<option value="millis">Time</option>';
                echo '<option value="op">Operation</option>';
                echo '<option value="ns">Collection</option>';
                echo '</select>';
                echo '<input type="submit" value="Sort" name="btnSort" id="btnSort" />';
                echo '<table style="width:100%"><thead><tr><th>S.No</th><th>Time</th><th>Operation</th><th>Collection</th><th>Date</th></tr></thead>';
          
                $sortOrder = $_POST['currentsortOrder'] == "asc" ? 1 : -1;
                
                $sorting = isset($_POST['sortBy']) ? array($_POST['sortBy'] => $sortOrder) : array('millis' => $sortOrder);
                foreach ($profileCollection->find()->sort($sorting) as $p) {
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
            
        </form>
    </body>
</html>
