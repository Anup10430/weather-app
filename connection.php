<?php
    $serverName = "sql308.infinityfree.com";
    $userName= "if0_41019475";
    $password = "Anup10infinity";
	$dbname = "if0_41019475_weatherapp";
    $conn = mysqli_connect($serverName, $userName, $password, $dbname);
    if($conn){
       // echo "Connection Successful <br>";
    }
    else{
        echo "Failed to connect".mysqli_connect_error();
    }

    $city = $_GET['q'] ?? 'Kathmandu';
    $apiKey = "9e1e3f07b74fdde45812bd9304c56c33";

    /*$createDatabase = "CREATE DATABASE IF NOT EXISTS weatherApp";
    if (mysqli_query($conn, $createDatabase)) {
      // echo "Database Created or already Exists <br>";
    } else {
        echo "Failed to create database <br>" . mysqli_connect_error();
    }
    
    // Select the created database
    mysqli_select_db($conn, 'weatherApp');*/
    
    $createTable = "CREATE TABLE IF NOT EXISTS weather_data(  
        id INT AUTO_INCREMENT PRIMARY KEY,
        city VARCHAR(100) NOT NULL,
        temperature DECIMAL(5,2) NOT NULL,
        pressure INT NOT NULL,
        humidity TINYINT NOT NULL,
        wind_speed DECIMAL(5,2) NOT NULL,
        weather_condition VARCHAR(100) NOT NULL,
        icon_code VARCHAR(10) NOT NULL,
        recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );";
    if (mysqli_query($conn, $createTable)) {
       // echo "Table Created or already Exists <br>";
    } else {
        echo "Failed to create database <br>" . mysqli_connect_error();
    }
 
    // Fetch latest data for the city
    $selectAllData = "SELECT * FROM weather_data where city = '$city' ORDER BY recorded_at DESC LIMIT 1";
    $result = mysqli_query($conn, $selectAllData);
    $row = mysqli_fetch_assoc($result);
    $rows = [];

    if ($row) {
        // Calculate time difference in seconds
        $time_diff = time() - strtotime($row['recorded_at']);
        /*
        time() = gives current time in seconds
        strtotime($row['recorded_at']) = converts recorded time into seconds
        $row['recorded_at'] = timestamp stored in DB
        */

        if($time_diff > 7200){
             $url = "https://api.openweathermap.org/data/2.5/weather?q=${city}&appid=${apiKey}&units=metric";
             $response = file_get_contents($url);
             $data = json_decode($response, true); 
             $city = $data['name'];
             $temperature = $data['main']['temp'];
             $pressure = $data['main']['pressure'];
             $humidity = $data['main']['humidity'];
             $wind_speed = $data['wind']['speed'];
             $weather_condition = $data['weather'][0]['description'];
             $icon_code = $data['weather'][0]['icon'];
         
             $insertData = "INSERT INTO weather_data (city, temperature, pressure, humidity, wind_speed, weather_condition, icon_code) VALUES('$city', '$temperature', '$pressure', '$humidity', '$wind_speed', '$weather_condition', '$icon_code')";
     
             if (mysqli_query($conn, $insertData)) {
                // echo "Data inserted Successfully";
             } else {
             echo "Failed to insert data" . mysqli_error($conn);
             }
      
            // Fetch the inserted data
            $result = mysqli_query($conn, $selectAllData);
            $row = mysqli_fetch_assoc($result);
            $rows[] = $row;
        } else{
            $rows[] = $row;
        }
        
    } else {
        // No data exists → fetch data from API
           $url = "https://api.openweathermap.org/data/2.5/weather?q=${city}&appid=${apiKey}&units=metric";
           $response = file_get_contents($url);
           $data = json_decode($response, true);
       
           $city = $data['name'];
           $temperature = $data['main']['temp'];
           $pressure = $data['main']['pressure'];
           $humidity = $data['main']['humidity'];
           $wind_speed = $data['wind']['speed'];
           $weather_condition = $data['weather'][0]['description'];
           $icon_code = $data['weather'][0]['icon'];
       
           $insertData = "INSERT INTO weather_data 
           (city, temperature, pressure, humidity, wind_speed, weather_condition, icon_code) 
           VALUES 
           ('$city', '$temperature', '$pressure', '$humidity', '$wind_speed', '$weather_condition', '$icon_code')";
           mysqli_query($conn, $insertData);
          
           // Fetch the inserted data
           $result = mysqli_query($conn, $selectAllData);
           $row = mysqli_fetch_assoc($result);
           $rows[] = $row;
    }

      // Prepare JSON response
      header('Content-Type: application/json');
      echo json_encode($rows);
?>