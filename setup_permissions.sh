#!/bin/bash

# Define the path to the chats directory and messages.json file
CHAT_DIR="chats"
MESSAGE_FILE="chats/messages.json"

# Ensure the chats directory exists
if [ ! -d "$CHAT_DIR" ]; then
  echo "Directory $CHAT_DIR does not exist. Creating it..."
  mkdir -p "$CHAT_DIR"
fi

# Set directory permissions (0777) so it is writable by all users (including the web server)
chmod 0777 "$CHAT_DIR"

# Ensure the messages.json file exists
if [ ! -f "$MESSAGE_FILE" ]; then
  echo "File $MESSAGE_FILE does not exist. Creating it..."
  touch "$MESSAGE_FILE"
  chmod 0666 "$MESSAGE_FILE"
else
  # Set file permissions (0666) so it is readable and writable by all users
  chmod 0666 "$MESSAGE_FILE"
fi

echo "Permissions have been set successfully."
