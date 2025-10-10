#!/usr/bin/env python3
"""
Docker Build Automation Script

Builds, tags, and (optionally) pushes Docker images for all services defined
in your docker-compose.yml file. Designed for CI/CD or local automation.

Usage:
  python scripts/python/build.py --push --tag v1.0.0
"""

import argparse
import subprocess
import sys
from typing import Optional

def run_command(cmd: list[str], cwd: Optional[str] = None):
    """Run a shell command and handle errors gracefully."""
    try:
        subprocess.run(cmd, cwd=cwd, check=True)
    except subprocess.CalledProcessError as e:
        print(f"Command failed: {' '.join(cmd)}")
        sys.exit(e.returncode)


def build_images(services: Optional[list[str]] = None):
    """Build Docker images for specified services."""
    print("Building Docker images...")
    cmd = ["docker", "compose", "build"]
    if services:
        cmd += services
    run_command(cmd)
    print("✅ Build complete!")


def tag_images(tag: str):
    """Tag built images with a version tag."""
    print(f"🏷️ Tagging images with '{tag}'...")
    cmd = ["docker", "compose", "images", "-q"]
    result = subprocess.run(cmd, capture_output=True, text=True, check=True)
    image_ids = [line.strip() for line in result.stdout.splitlines() if line.strip()]
    for image_id in image_ids:
        run_command(["docker", "tag", image_id, f"myblog:{tag}"])
    print("Tagging complete!")


def push_images(tag: str):
    """Push images to remote container registry."""
    print(f"Pushing images with tag '{tag}'...")
    run_command(["docker", "compose", "push"])
    print("Push complete!")


def main():
    parser = argparse.ArgumentParser(description="Build and push Docker images")
    parser.add_argument("--push", action="store_true", help="Push images after building")
    parser.add_argument("--tag", default="latest", help="Tag name for images")
    parser.add_argument("--services", nargs="*", help="Optional list of services to build")
    args = parser.parse_args()

    # Ensure Docker is available
    if subprocess.run(["docker", "info"], stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL).returncode != 0:
        print("Docker daemon is not running or not accessible.")
        sys.exit(1)

    build_images(args.services)

    if args.tag:
        tag_images(args.tag)

    if args.push:
        push_images(args.tag)


if __name__ == "__main__":
    main()
