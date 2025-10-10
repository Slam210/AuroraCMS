#!/usr/bin/env python3
"""
Project Task Runner

Centralized CLI for managing your Headless CMS Blog Platform.
If no arguments are provided, runs all tasks in order (build → backup → analyze).

Usage:
  python scripts/python/manage.py           # Runs all tasks in order
  python scripts/python/manage.py build     # Runs only the build task
  python scripts/python/manage.py backup    # Runs only the backup task
  python scripts/python/manage.py build --push --tag v1.0.0  # With args
"""

import argparse
import subprocess
import sys
import os
from pathlib import Path
from typing import Optional

# Ordered list of scripts (defines execution order)
SCRIPTS = {
    "build": "build.py",
    "backup": "backup.py",
}

def run_script(script_name: str, args: Optional[list[str]] = None):
    """Run another Python script as a subprocess."""
    script_path = Path(__file__).parent / script_name
    if not script_path.exists():
        print(f"Script not found: {script_path}")
        sys.exit(1)

    cmd = [sys.executable, str(script_path)] + (args or [])
    print(f"\nRunning {script_name} {' '.join(args) if args else ''}")
    try:
        subprocess.run(cmd, check=True)
    except subprocess.CalledProcessError as e:
        print(f"Script {script_name} failed with exit code {e.returncode}")
        sys.exit(e.returncode)

def main():
    parser = argparse.ArgumentParser(
        description="Headless CMS Blog Platform Task Runner"
    )
    parser.add_argument(
        "task",
        nargs="?",
        choices=SCRIPTS.keys(),
        help="Task to run (optional): build, backup, analyze",
    )
    parser.add_argument(
        "task_args",
        nargs=argparse.REMAINDER,
        help="Additional arguments passed to the selected task",
    )
    args = parser.parse_args()

    # No task provided → run all scripts in order
    if not args.task:
        print("No task provided — running all scripts in order:")
        for task, script in SCRIPTS.items():
            print(f"   • {task}")
        for task, script in SCRIPTS.items():
            run_script(script)
        print("\nAll tasks completed successfully!")
        return

    # Run a specific task
    run_script(SCRIPTS[args.task], args.task_args)

if __name__ == "__main__":
    main()
