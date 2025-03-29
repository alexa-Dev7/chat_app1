#!/bin/bash

# Define file and directory paths
FILE_PATH="/var/www/html/chats/messages.json"
DIRECTORY_PATH="/var/www/html/chats"

# Check if the file exists
if [ -f "$FILE_PATH" ]; then
    echo "File $FILE_PATH found, setting permissions."
else
    echo "File $FILE_PATH not found! Please check the path."
    exit 1
fi

# Change ownership to the web server user (usually 'www-data' for Apache or Nginx)
echo "Changing ownership of $FILE_PATH and $DIRECTORY_PATH to www-data..."
sudo chown -R www-data:www-data "$DIRECTORY_PATH"

# Set appropriate permissions for the file (rw for owner and group, r for others)
echo "Setting permissions for $FILE_PATH..."
sudo chmod 664 "$FILE_PATH"

# Set appropriate permissions for the directory (rw for owner and group, r for others)
echo "Setting permissions for $DIRECTORY_PATH..."
sudo chmod 775 "$DIRECTORY_PATH"

# Verify the permissions have been applied
echo "Permissions for $FILE_PATH:"
ls -l "$FILE_PATH"

echo "Permissions for $DIRECTORY_PATH:"
ls -ld "$DIRECTORY_PATH"

echo "Permissions have been updated successfully!"
