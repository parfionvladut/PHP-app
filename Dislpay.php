
// Function to fetch and display records in batches
function fetchAndDisplayRecordsBatch($conn, $offset, $records_per_page) {

	if ($b1=="Sortare")
    $qstring_main = "SELECT m.id, m.cod_firma, m.nume_firma, m.tel1, m.tel1_pers, m.statut, m.cemt, m.rute, m.tip_masini, m.obs_masini, m.tip_marfa, m.shitlist FROM main AS m WHERE m.reprezentant LIKE '%$useriddb%' $cond ORDER BY m.ultima_contactare DESC LIMIT $offset, $records_per_page";
    elseif ($b1=="Shitlist")
	$qstring_main = "SELECT m.id, m.cod_firma, m.nume_firma, m.tel1, m.tel1_pers, m.statut, m.cemt, m.rute, m.tip_masini, m.obs_masini, m.tip_marfa, m.shitlist FROM main AS m WHERE m.shitlist='1'  ORDER BY m.ultima_contactare DESC LIMIT $offset, $records_per_page";
	else
	$qstring_main = "SELECT m.id, m.cod_firma, m.nume_firma, m.tel1, m.tel1_pers, m.statut, m.cemt, m.rute, m.tip_masini, m.obs_masini, m.tip_marfa, m.shitlist FROM main AS m WHERE m.reprezentant LIKE '%$useriddb%' $cond ORDER BY m.ultima_contactare DESC LIMIT $offset, $records_per_page";
	
	
	$result_main = mysqli_query($conn, $qstring_main) or die("Error executing main query: " . mysqli_error($conn));

    $qstring_contactari = "SELECT c.id_firma, c.data AS data_contact, c.nota AS nota_contact FROM contactari AS c";
    $result_contactari = mysqli_query($conn, $qstring_contactari) or die("Error executing contactari query: " . mysqli_error($conn));

    // Initialize an empty array to store the results
    $contactari_data = array();

    // Fetch data from the contactari table and store it in the array
    while ($row_contactari = mysqli_fetch_array($result_contactari)) {
        $id_firma = $row_contactari['id_firma'];
        $contactari_data[$id_firma] = array(
            'data_contact' => $row_contactari['data_contact'],
            'nota_contact' => $row_contactari['nota_contact']
        );
    }

    echo "<table border='1'>";
    echo "<tr>";
    echo "<td>Cod firma</td>";
    echo "<td>Nume firma</td>";
    echo "<td>Numar telefon primar</td>";
    echo "<td>Persoana de contact</td>";
    echo "<td>Data ultimei contactari</td>";
    echo "<td>Nota ultimei contactari</td>";
    echo "<td>Rute</td>";
    echo "<td>Tip masini</td>";
    echo "<td>Observatii masini</td>";
    echo "<td>CEMT</td>";
    echo "<td>Casa de expeditii</td>";
    echo "</tr>";

    $i = 0;
    while ($row = mysqli_fetch_array($result_main)) {
        $i++;
        $id = $row['id'];
        $cod_firma = $row['cod_firma'];
        $nume_firma = $row['nume_firma'];
        $telefon = $row['tel1'];
        $persoana = $row['tel1_pers'];
        // Get corresponding data from the contactari table based on the common key
        $data_ultimei_contactari = isset($contactari_data[$id]['data_contact']) ? $contactari_data[$id]['data_contact'] : '';
        $nota_ultimei_contactari = isset($contactari_data[$id]['nota_contact']) ? $contactari_data[$id]['nota_contact'] : '';
        $rute = $row['rute'];
        $tip_masini = $row['tip_masini'];
        $obs_masini = $row['obs_masini'];
        $tip_marfa = $row['cemt'];
        $casa_de_expeditii = $row['statut'];
            if ($casa_de_expeditii=="4") $cde = 'DA'; else $cde = 'NU';

        $shitlist = $row['shitlist'];

        if ($i % 2)  echo "<tr>"; else echo "<tr bgcolor=\"99CCFF\">";
        if ($shitlist==1) $efect = "<strike>"; else $efect = "&nbsp;";
        echo "<td>$cod_firma</td>";
        echo "<td>$efect <a href=detalii2.php?id=$id target=_NEW>$nume_firma</a></td>";
        echo "<td>$telefon</td>";
        echo "<td>$persoana</td>";
        echo "<td>$data_ultimei_contactari</td>";
        echo "<td>$nota_ultimei_contactari</td>";
        echo "<td>"; //rute

        $rexp = explode(",", $rute);
        $rnr = substr_count($rute, ",");
        for ($k = 0; $k < $rnr; $k++) {
            $numar = $rexp[$k];
            $result1 = mysqli_query($conn, "SELECT tara FROM rute WHERE id = $numar") or die("Error in query: " . mysqli_error($conn));
            $row1 = mysqli_fetch_row($result1);
            $numetara = $row1[0];
            $numetarax = substr($numetara, 0, 3);
            echo "$numetarax <br>";
        }
        echo "</td>";
        echo "<td>";
        if ($tip_masini == 0) {
            echo "&nbsp;";
        } elseif ($tip_masini == 1) {
            echo "20 T";
        } elseif ($tip_masini == 2) {
            echo "3 T";
        } elseif ($tip_masini == 3) {
            echo "Grupaj";
        } elseif ($tip_masini == 4) {
            echo "20+3 T";
        }
        echo "</td>";
        echo "<td>$obs_masini</td>";
        echo "<td>$cemt</td>";
        echo "<td>$cde</td>";
        echo "</tr>";
    }

    echo "</table>";
}

// Fetch and display records for the current page
fetchAndDisplayRecordsBatch($conn, $offset, $records_per_page, $useriddb, $cond, $b1);

// Output pagination links
echo "<div>";
for ($i = 1; $i <= $total_pages; $i++) {
    if ($i == $current_page) {
        echo "<span>$i</span> ";
    } else {
        // Get the current URL
        $current_url = $_SERVER['REQUEST_URI'];
        if($current_url=="/colaboratori/login.php"){
        echo "<a href='firstpage.php?limit=A&categdb=2&useriddb=$useriddb&b1=$b1&page=$i'>$i</a> ";
        }else{

                if (!isset($_GET['page']) || empty($_GET['page'])) {

                        $new_url = $current_url . (strpos($current_url, '?') !== false ? '&' : '?') . "page=$i";

                }else{
                        //Replace the existing page parameter with the new page number
                        $new_url = preg_replace('/([?|&])page=\d+/', '$1page=' . $i, $current_url);


                }
        echo "<a href='$new_url'>$i</a> ";
        }
    }
}
echo "</div>";

