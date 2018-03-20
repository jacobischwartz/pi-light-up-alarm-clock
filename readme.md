**Light Up Alarm Clock**

This project is a home grown alarm clock.
The clock should be built using a Raspberry Pi for the controller and 
web-based settings interface, and LEDs on the Pi to implement the alarm action.
The alarm clock will gradually light up the room,
resulting in a gradual wakeup process - and a happy start to the day.
This controller allows different alarm times to be set for each day of the week.

**How to use this:**
- Install any Linux distribution on your Pi.
- Connect the Pi to your home WIFI. Give it a static IP address on the network.
- Configure your web server of choice on the Pi. Apache HTTPD with PHP 5+ is easy.
- Clone this repo into your web root. Set the permissions permissively;
The app needs to be able to create and modify files in it's own directory because
it uses a very basic flat file to store configuration.
- index.php is your human interface, the form that controls the settings.
- cron.php should be called once every minute.
- Check out the hardware diagrams in the instructions folder.
- Stash the project inside a bedside lamp shade to evenly light the room.
- Access the settings form by using the Pi's local IP address as the URL. Save the URL to your phone's home screen for easy access.
- Wake happily every morning.
