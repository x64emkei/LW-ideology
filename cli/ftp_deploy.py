#!/usr/bin/env python3
"""
LagunaWISE — Ideology Division: Automated FTP Deployment Tool
Uploads local project files to InfinityFree hosting (/htdocs/)
"""

import os
import sys
import ftplib
import time

FTP_HOST = "ftpupload.net"
FTP_PORT = 21
FTP_USER = "if0_43456590"
FTP_PASS = "Goq4bpkrc1"
REMOTE_ROOT = "/htdocs"

LOCAL_DIR = os.path.abspath(os.path.join(os.path.dirname(__file__), "..", "..", "LW-ideology-infinityfree"))
if not os.path.isdir(LOCAL_DIR):
    LOCAL_DIR = os.path.abspath(os.path.join(os.path.dirname(__file__), ".."))

# Files/folders to exclude from upload
EXCLUDE_DIRS = {".git", ".agents", "tests", ".idea", ".vscode", "__pycache__"}
EXCLUDE_FILES = {".DS_Store", "Thumbs.db", "ftp_deploy.py", "ftp-deploy.ps1"}

def connect_ftp():
    print(f"[*] Connecting to {FTP_HOST}:{FTP_PORT} as {FTP_USER}...")
    ftp = ftplib.FTP()
    ftp.connect(FTP_HOST, FTP_PORT, timeout=30)
    ftp.login(FTP_USER, FTP_PASS)
    ftp.set_pasv(True)
    print(f"[+] Connected to InfinityFree FTP successfully: {ftp.getwelcome()}")
    return ftp

def ensure_remote_dir(ftp, remote_path):
    parts = remote_path.strip("/").split("/")
    current = ""
    for part in parts:
        current += "/" + part
        try:
            ftp.cwd(current)
        except ftplib.error_perm:
            try:
                ftp.mkd(current)
                print(f"[+] Created remote directory: {current}")
                ftp.cwd(current)
            except ftplib.error_perm as e:
                # Might already exist or permission error
                pass

def upload_project(local_base, remote_base):
    start_time = time.time()
    ftp = connect_ftp()

    ensure_remote_dir(ftp, remote_base)
    uploaded_count = 0
    total_bytes = 0

    print(f"[*] Source directory: {local_base}")
    print(f"[*] Target directory: {remote_base}")
    print("-" * 65)

    for root, dirs, files in os.walk(local_base):
        # Prune excluded directories
        dirs[:] = [d for d in dirs if d not in EXCLUDE_DIRS]

        rel_path = os.path.relpath(root, local_base)
        if rel_path == ".":
            remote_dir = remote_base
        else:
            remote_dir = remote_base + "/" + rel_path.replace("\\", "/")

        ensure_remote_dir(ftp, remote_dir)
        ftp.cwd(remote_dir)

        for filename in files:
            if filename in EXCLUDE_FILES or filename.endswith(".zip"):
                continue

            local_file = os.path.join(root, filename)
            file_size = os.path.getsize(local_file)

            try:
                with open(local_file, "rb") as f:
                    ftp.storbinary(f"STOR {filename}", f)
                uploaded_count += 1
                total_bytes += file_size
                rel_file = os.path.relpath(local_file, local_base).replace("\\", "/")
                print(f" -> [{uploaded_count:03d}] {rel_file} ({file_size:,} bytes)")
            except Exception as ex:
                print(f" [!] Error uploading {filename}: {ex}")
                # Try to reconnect if connection dropped
                try:
                    ftp.voidcmd("NOOP")
                except Exception:
                    print(" [*] Reconnecting FTP session...")
                    ftp = connect_ftp()
                    ftp.cwd(remote_dir)
                    with open(local_file, "rb") as f:
                        ftp.storbinary(f"STOR {filename}", f)
                    uploaded_count += 1
                    total_bytes += file_size
                    print(f" -> [{uploaded_count:03d}] {rel_file} (recovered)")

    ftp.quit()
    elapsed = time.time() - start_time
    print("-" * 65)
    print(f"[SUCCESS] Transfer complete! Uploaded {uploaded_count} files ({total_bytes / (1024*1024):.2f} MB) in {elapsed:.1f}s.")
    print(f"[+] Website is now live and updated on InfinityFree!")

if __name__ == "__main__":
    upload_project(LOCAL_DIR, REMOTE_ROOT)
