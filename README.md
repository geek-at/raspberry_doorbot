# Doorman script will send a chat message to a rocket chat channel when a door was opened
by [Christian Haschek](https://blog.haschek.at)

![The result](https://www.pictshare.net/a3939806c5.jpg)

## What does it do?
- Senses when your door was opened
- Sends a chat message in a rocket chat channel of your coice (own servers supported)
- Tells you how long the door was opened
- Tells you how much time has passed since the door was last closed so you know how long you were out or in

### Requirements

- Raspberry Pi
- Reed Switch
- 1x 10k ohm resistor
- Channel ID of the channel your bot is going to post the message

### STEPS

##### Connect the Reed switch to the Raspberry Pi

Connect the reed switch acording to these schematics:

[![diagram](https://www.pictshare.net/300/8c24794483.jpg)]((https://www.pictshare.net/store/8c24794483.jpg))

##### Step 1: Install php and the "gpio" command

```bash
apt-get install wiringpi php5-cli
echo 'w1-gpio' >> /etc/modules
modprobe w1-gpio
```

##### Step 2: Get the script

```
git clone https://github.com/chrisiaut/raspberry_doorbot.git
```

##### Step 3:
Edit the settings in the door_chatbot.php file and add your channel, server and login credentials

#### Final step: Run script on startup

```crontab -e```

insert this at the end of the file

```@reboot php /path/to/your/door_chatbot.php >> /var/log/doorbot.log```


## Should be working now
![Bot in action](https://www.pictshare.net/a3939806c5.jpg)