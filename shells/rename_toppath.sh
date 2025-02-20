#!/bin/bash

# Script to replace app name strings in iOS and Android distribution files
# Usage: ./rename_toppath.sh [-n] <old_name> <new_name>
# -n: dry-run mode (preview changes without making them)

# Default directories to search
directories=("ios_distributions" "android_distributions")

# Check if required directories exist
missing_dirs=()
for dir in "${directories[@]}"; do
    if [ ! -d "$dir" ]; then
        missing_dirs+=("$dir")
    fi
done

if [ ${#missing_dirs[@]} -gt 0 ]; then
    echo "Error: Required directories not found:"
    printf '  - %s\n' "${missing_dirs[@]}"
    echo "Please run this script from the top directory and ensure these directories exist."
    echo "Example: cd /path/to/project && ./shells/rename_toppath.sh [-n] <old_name> <new_name>"
    exit 1
fi

# Dry-run flag
dry_run=false

# Function to show usage
show_usage() {
    echo "Usage: $0 [-n] <old_name> <new_name>"
    echo "Options:"
    echo "  -n    Dry-run mode (preview changes without making them)"
    echo "Example:"
    echo "  $0 AIRobot KT"
    echo "  $0 -n AIRobot KT"
    exit 1
}

# Parse arguments
while getopts "n" opt; do
    case $opt in
        n) dry_run=true ;;
        *) show_usage ;;
    esac
done

# Shift past the options
shift $((OPTIND-1))

# Check if we have the required arguments
if [ $# -ne 2 ]; then
    show_usage
fi

# Get the find and replace strings
find_str="$1"
replace_str="$2"

# Print operation mode
if [ "$dry_run" = true ]; then
    echo "Running in DRY-RUN mode (no changes will be made)"
fi
echo "Will replace '$find_str' with '$replace_str'"

# Counter for affected files
affected_files=0

# Iterate over each directory
for dir in "${directories[@]}"; do
    echo -e "\nProcessing directory: $dir"
    # Find files containing the string, excluding .git directory
    find "$dir" -type f -not -path "*.git*" -exec grep -l "$find_str" {} \; | while read -r file; do
        ((affected_files++))
        echo "Found match in file: $file"
        if [ "$dry_run" = false ]; then
            # Create backup file
            cp "$file" "${file}.bak"
            # Perform replacement
            if sed -i "" "s/$find_str/$replace_str/g" "$file"; then
                echo "✓ Updated: $file"
            else
                echo "✗ Error updating: $file"
                # Restore from backup
                mv "${file}.bak" "$file"
            fi
            # Remove backup if successful
            rm -f "${file}.bak"
        else
            echo "Dry-run: Would replace '$find_str' with '$replace_str' in $file"
            # Show preview of changes
            echo "Preview of changes:"
            grep -l "$find_str" "$file" | xargs grep --color=always "$find_str"
        fi
    done
done

# Summary
echo -e "\nOperation complete!"
echo "Files affected: $affected_files"
if [ "$dry_run" = true ]; then
    echo "No changes were made (dry-run mode)"
fi
