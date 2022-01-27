<?php

/**
 * Plugin Name:       ABtesting
 * Plugin URI:        https://localhost/wp-admin/plugins.php
 * Description:       Plugin per Wordpress che consente di fare A/B testing randomizzando gli utenti su versioni diverse del sito
 * Version:           1.0
 * Requires PHP:      7.2
 * Author:            Giovanni Casini & Cosimo Tanganelli
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Domain Path:       /languages
 */

function demo_settings()     // definisce impostazioni del plugin
{
    add_settings_section("section", "Impostazioni", null, "demo");
    add_settings_field("theme1", "Tema 1", "theme_1_select", "demo", "section");
    add_settings_field("theme2", "Tema 2", "theme_2_select", "demo", "section");
    add_settings_field("goalurl", "URL Traguardo separati da virgole", "urlTraguardo", "demo", "section");
    register_setting("section", "theme1");
    register_setting("section", "theme2");
    register_setting("section", "goalurl");
}

function theme_1_select()  // seleziona primo tema
{
    $themes = wp_get_themes();
    if (count($themes) > 1) {
        $theme_names = array_keys($themes);
    }
    $html = '';
    foreach ($theme_names as $theme) {
        $html .= "<option value='" . $theme . "'" . selected(get_option('theme1'), $theme, false) . ">" . $theme . "</option>";
    }
?>
    <select name="theme1">
        <?php echo $html; ?>
    </select>
<?php
}

function theme_2_select()   // seleziona secondo tema
{
    $themes = wp_get_themes();
    if (count($themes) > 1) {
        $theme_names = array_keys($themes);
    }
    $html = '';
    foreach ($theme_names as $theme) {
        $html .= "<option value='" . $theme . "'" . selected(get_option('theme2'), $theme, false) . ">" . $theme . "</option>";
    }

?>
    <select name="theme2">
        <?php echo $html; ?>
    </select>
<?php

}

function urlTraguardo()
{   // seleziona URL di arrivo per calcolo statistiche
    $html = "<input type='text' value='" . get_option('goalurl') . "' name='goalurl'></input>";
    echo $html;
}

add_action("admin_init", "demo_settings");

function demo_page()   // crea pagina impostazioni del plugin
{
?>
    <div class="wrap">
        <h1>ABTesting</h1>

        <form method="post" action="options.php">
            <?php

            settings_fields("section");


            do_settings_sections("demo");
            submit_button();
            ?>
        </form>
    </div>
<?php
}

function menu_item()  // crea submenu del plugin
{
    add_submenu_page("options-general.php", "ABTesting Settings", "ABtesting", "manage_options", "demo", "demo_page");
}

function my_admin_menu()
{
    add_menu_page(
        __('Stats', 'my-textdomain'),
        __('ABTesting Statistiche', 'my-textdomain'),
        'manage_options',
        'sample-page',
        'my_admin_page_contents',
        'dashicons-schedule',
        3
    );
}

add_action('admin_menu', 'my_admin_menu');

function get_data_query($sql)
{
    $servername = "localhost";
    $username = "root";
    $password = "root";
    $dbname = "abtesting";
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error)
        die("Connection failed: " . $conn->connect_error);
    $result = $conn->query($sql);
    $query_array = array();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            array_push($query_array, array($row["theme"], $row["avgtime"], $row["avgclicks"], $row["webpage"]));
        }

        return $query_array;
    }
    $conn->close();
}

function get_time_query($sql)
{
    $servername = "localhost";
    $username = "root";
    $password = "root";
    $dbname = "abtesting";
    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error)
        die("Connection failed: " . $conn->connect_error);
    $result = $conn->query($sql);
    $query_array = array();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            array_push($query_array, array($row["webpage"], $row["numuser"]));
        }
        return $query_array;
    }
}
//SELECT userid, SUM(timespent), COUNT(webpage) FROM `testingdata` GROUP BY userid
//SELECT AVG(timesum), AVG(webcount) FROM (SELECT userid, SUM(timespent) AS timesum, COUNT(webpage) AS webcount FROM `testingdata` GROUP BY userid) AS inner_query
//SELECT theme, AVG(timesum), AVG(webcount) FROM (SELECT userid, theme, SUM(timespent) AS timesum, COUNT(webpage) AS webcount FROM `testingdata` GROUP BY userid, theme) AS inner_query GROUP BY theme
//SELECT theme, AVG(timesum) AS avgtime, AVG(webcount) AS avgclicks FROM (SELECT userid, theme, SUM(timespent) AS timesum, COUNT(webpage) AS webcount FROM `testingdata` GROUP BY userid, theme) AS inner_query WHERE theme = "twentynineteen" OR theme = "twentytwenty" GROUP BY theme
//SELECT webpage, userid, theme, SUM(timespent) AS timesum, COUNT(webpage) AS webcount FROM `testingdata` GROUP BY userid, theme, webpage
//SELECT userid, webpage FROM testingdata WHERE webpage IS NOT NULL
//SELECT testingdata.userid, theme, SUM(timespent) AS timesum, COUNT(testingdata.webpage) AS webcount FROM `testingdata` INNER JOIN (SELECT userid, webpage FROM testingdata WHERE webpage IS NOT NULL) AS inner_query ON inner_query.userid = testingdata.userid GROUP BY testingdata.userid, theme
//SELECT inner_query.webpage, theme, SUM(timespent) AS timesum, COUNT(testingdata.userid) AS webcount FROM `testingdata` INNER JOIN (SELECT userid, webpage FROM testingdata WHERE webpage IS NOT NULL) AS inner_query ON inner_query.userid = testingdata.userid GROUP BY theme, inner_query.webpage
//SELECT webpage, theme, AVG(timesum) AS avgtime, AVG(webcount) AS avgclicks FROM (SELECT inner_query.webpage, theme, SUM(timespent) AS timesum, COUNT(testingdata.userid) AS webcount FROM `testingdata` INNER JOIN (SELECT userid, webpage FROM testingdata WHERE webpage IS NOT NULL) AS inner_query ON inner_query.userid = testingdata.userid GROUP BY theme, inner_query.webpage) AS inner_inner_query WHERE theme = "twentytwenty" OR theme = "twentytwentyone" GROUP BY webpage, theme
//SELECT inner_query.webpage, testingdata.userid, theme, SUM(timespent) AS timesum, COUNT(testingdata.userid) AS webcount FROM `testingdata` INNER JOIN (SELECT userid, webpage FROM testingdata WHERE webpage IS NOT NULL) AS inner_query ON inner_query.userid = testingdata.userid GROUP BY theme, inner_query.webpage, testingdata.userid
//SELECT webpage, COUNT(userid) FROM testingdata WHERE webpage IS NOT NULL GROUP BY webpage

function my_admin_page_contents()
{
    $sql_query = 'SELECT webpage, theme, AVG(timesum) AS avgtime, AVG(webcount) AS avgclicks FROM (SELECT inner_query.webpage, testingdata.userid, theme, SUM(timespent) AS timesum, COUNT(testingdata.userid) AS webcount FROM `testingdata` INNER JOIN (SELECT userid, webpage FROM testingdata WHERE webpage IS NOT NULL) AS inner_query ON inner_query.userid = testingdata.userid GROUP BY theme, inner_query.webpage, testingdata.userid) AS inner_inner_query WHERE theme = "' . get_option("theme1") . '" OR theme = "' . get_option("theme2") . '" GROUP BY webpage, theme';
    $sql_query_1 = 'SELECT webpage, COUNT(userid) AS numuser FROM testingdata WHERE webpage IS NOT NULL GROUP BY webpage';
?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
    <h1>
        <?php esc_html_e('ABTesting Statistiche', 'my-plugin-textdomain'); ?>
    </h1>
    <h4 id="userCount"></h4>
    <div id="chart-container-container">

    </div>
    <script>
        var sqlArray = <?php echo json_encode(get_data_query($sql_query)); ?>;
        var sqlArray1 = <?php echo json_encode(get_time_query($sql_query_1)); ?>;
        for (var i = 0; i < sqlArray.length; i++) {
            document.getElementById("chart-container-container").innerHTML += '<div class="chart-container" style="width: 45%; display: inline-block;"><canvas id="chart' + i + '"></canvas></div>';
        }
        for (var i = 0; i < sqlArray.length / 2; i++) { //for each URL
            var context1 = document.getElementById('chart' + String(i * 2)).getContext('2d');
            var context2 = document.getElementById('chart' + String(i * 2 + 1)).getContext('2d');
            try {
                var data1 = {
                    labels: ["Tempo speso in navigazione (secondi)"],
                    datasets: [{
                            label: sqlArray[i * 2][0],
                            backgroundColor: "#CA2E55",
                            data: [sqlArray[i * 2][1]]
                        },
                        {
                            label: sqlArray[i * 2 + 1][0],
                            backgroundColor: "#6457A6",
                            data: [sqlArray[i * 2 + 1][1]]
                        },
                    ]
                };
            } catch (e) {
                console.warn("Not enough data to draw graph!")
            }
            try {
                var data2 = {
                    labels: ["Numero di Click"],
                    datasets: [{
                            label: sqlArray[i * 2][0],
                            backgroundColor: "#CA2E55",
                            data: [sqlArray[i * 2][2]]
                        },
                        {
                            label: sqlArray[i * 2 + 1][0],
                            backgroundColor: "#6457A6",
                            data: [sqlArray[i * 2 + 1][2]]
                        },
                    ]
                };
            } catch (e) {
                console.warn("Not enough data to draw graph!")
            }
            var chart1 = new Chart(context1, {
                type: 'bar',
                data: data1,
                options: {
                    plugins: {
                        title: {
                            display: true,
                            text: sqlArray[i * 2][3]
                        }
                    },
                    barValueSpacing: 20
                }
            });
            var chart2 = new Chart(context2, {
                type: 'bar',
                data: data2,
                options: {
                    plugins: {
                        title: {
                            display: true,
                            text: sqlArray[i * 2 + 1][3]
                        }
                    },
                    barValueSpacing: 20
                }
            });
        }
        var totalUserText = "Numero utenti arivati a URL traguardo: <br>";
        for (var i = 0; i < sqlArray1.length; i++) {
            totalUserText += sqlArray1[i][0] + ": " + sqlArray1[i][1];
            totalUserText += "<br>";
        }
        document.getElementById("userCount").innerHTML = totalUserText;
    </script>

<?php
}

add_action("admin_menu", "menu_item");

function get_current_url()
{
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') $url = "https://";
    else $url = "http://";
    $url .= $_SERVER['HTTP_HOST'];
    $url .= $_SERVER['REQUEST_URI'];
    return $url;
}

function perform_query($sql)
{
    $servername = "localhost";
    $username = "root";
    $password = "root";
    $dbname = "abtesting";
    // Create connection
    $conn = mysqli_connect($servername, $username, $password, $dbname);
    // Check connection
    if (!$conn)
        die("Connection failed: " . mysqli_connect_error());
    if (!mysqli_query($conn, $sql))
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    mysqli_close($conn);
}

function cookie_manager()
{   // gestisce cookie per tenere traccia dati utente
    $themes_array = [get_option('theme1'), get_option('theme2')];
    $rand_index = array_rand($themes_array);
    $user_theme = $themes_array[$rand_index];

    if (!isset($_COOKIE['userid'])) {
        setcookie('userid', uniqid('', true), 0, '/');
        setcookie('theme', $user_theme, 0, '/');
        setcookie('currenturl', get_current_url(), 0, '/');
        setcookie('time_started', time(), 0, '/');
        switch_theme($user_theme);
    } else {
        if (get_current_url() != $_COOKIE['currenturl'] && $_COOKIE["goal"] != "true") {
            $url_array = explode(',', get_option('goalurl'));
            $cookiedb = "NULL";
            if (in_array(get_current_url(), $url_array)) {
                setcookie('goal', 'true', 0, '/');
                $cookiedb = "'" . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" . "'";
            }
            $total_time = time() - $_COOKIE["time_started"];
            setcookie('time_started', time(), 0, '/');
            $sql = "INSERT INTO testingdata (timespent, userid, theme, webpage)
            VALUES ('" . $total_time . "', '" .  $_COOKIE["userid"] . "', '" . $_COOKIE["theme"] . "'," . $cookiedb . ")";
            perform_query($sql);
            setcookie('currenturl', get_current_url(), 0, '/');
            $total_time = 0;
        }
    }
}
add_action('init', 'cookie_manager');
