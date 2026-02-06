const apiKey = "9e1e3f07b74fdde45812bd9304c56c33";

// Function to fetch a city based on user input
async function fetchWeatherData() {
  const city = document.getElementById("cityInput").value;

  if (city == "") {
    alert("Please enter a city name");
    return;
  }

  const apiUrl = `https://myweatherapp.free.nf/connection.php?q=${city}`;
  let data;

  try {
    // Check if browser is online
    if(navigator.onLine){
      // ONLINE: fetch from API
      const response = await fetch(apiUrl);
      data = await response.json();
      
      // Save fetched data to localStorage
      localStorage.setItem(city,JSON.stringify(data));
    } else{
      // OFFLINE: get data from local storage
      data = JSON.parse(localStorage.getItem(city));
    }
    
    // If no data exists (first time offline)
    if (!data) {
      alert("No cached data available. Please connect to the internet.");
      return;
    }

    // PHP returns an array -> use first object
    if (data.length > 0) {
        showWeather(data[0]);
    } else {
        alert("No data found");
    }

  } catch (err) {
    console.error(err);
    alert("Error fetching weather data");
  }
}

// Function to display weather result
function showWeather(data) {
  const weatherDiv = document.getElementById("weatherResult");

  const city = data.city || data.name;
  const temp = Math.round(data.temperature || data.main.temp);
  const pressure = data.pressure || data.main.pressure;
  const humidity = data.humidity || data.main.humidity;
  const windSpeed = data.wind_speed || data.wind.speed;
  const condition = data.weather_condition || data.weather[0].description;
  const iconCode = data.icon_code || data.weather[0].icon;
  const iconUrl = `https://openweathermap.org/img/wn/${iconCode}@2x.png`;

  weatherDiv.innerHTML = `
        <h2>${city}</h2>
        <img src="${iconUrl}" alt="Weather icon">
        <p>${condition}</p>

        <div class="weather-details">
            <div class="detail-row">
                <span>Temperature</span>
                <span>${temp} °C</span>
            </div>

            <div class="detail-row">
                <span>Pressure</span>
                <span>${pressure} hPa</span>
            </div>

            <div class="detail-row">
                <span>Humidity</span>
                <span>${humidity} %</span>
            </div>

            <div class="detail-row">
                <span>Wind Speed</span>
                <span>${windSpeed} m/s</span>
            </div>
        </div>
    `;
}

const button = document.getElementById("btn");
button.addEventListener("click", fetchWeatherData);

// Function to fetch default weather
async function fetchDefaultWeather(city) {
  const apiUrl = `https://api.openweathermap.org/data/2.5/weather?q=${city}&appid=${apiKey}&units=metric`;

  try {
    const response = await fetch(apiUrl);
    const data = await response.json();
    showWeather(data);
  } catch (err) {
    console.error("Error fetching default weather:", err);
  }
}

// Trigger Kathmandu on page load
window.addEventListener("load", () => {
  fetchDefaultWeather("Kathmandu");
});

