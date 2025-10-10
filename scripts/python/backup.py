#!/usr/bin/env python3
import subprocess
import sys
import datetime
from pathlib import Path

def run_command(cmd: list[str]):
    """Run a shell command and exit gracefully if it fails."""
    try:
        subprocess.run(cmd, check=True)
    except subprocess.CalledProcessError as e:
        print(f"❌ Command failed: {' '.join(cmd)}")
        sys.exit(e.returncode)

def backup_database():
    """Backup MySQL database from the WordPress container."""
    timestamp = datetime.datetime.now().strftime("%d%m%Y_%H%M%S")
    backup_file = Path(f"backups/backup_{timestamp}.sql")

    print(f"Backing up database to {backup_file}...")

    # Check if db service is running
    ps = subprocess.run(
        ["docker", "compose", "ps", "-q", "db"],
        capture_output=True, text=True
    )

    if not ps.stdout.strip():
        print("The 'db' service is not running. Start containers first:")
        print("   docker compose up -d")
        sys.exit(1)

    # Run mysqldump from within the db service
    with open(backup_file, "w") as f:
        try:
            subprocess.run([
                "docker", "compose", "exec", "-T", "db",
                "mysqldump", "-u", "root", "-proot", "wordpress"
            ], stdout=f, check=True)
        except subprocess.CalledProcessError:
            print("Database dump failed.")
            sys.exit(1)

    print(f"Database backup saved as {backup_file}")

if __name__ == "__main__":
    backup_database()
