# Remote Downloader PHP

**RemoteDownloaderPHP** is a simple PHP script designed to download large files directly to your server from any remote URL.  

This tool is ideal for environments with PHP or cPanel limitations, allowing you to bypass client-side download restrictions and save files directly on your hosting.

---

## Features

- Fetches remote file metadata (Content-Type, file size, resumable support)
- User-friendly confirmation before download
- Downloads large files to server storage with unlimited execution time
- Handles HTTP redirects and errors gracefully
- Simple and clean single PHP file implementation
- Logs download activity for troubleshooting
- Supports custom filenames
- Minimal dependencies — works with standard PHP and cURL

---

## Installation

1. Clone or download this repository to your server directory (e.g., `/public_html/RemoteDownloaderPHP`).
2. Make sure the `downloads/` directory exists and is writable by PHP.
3. Adjust `php.ini` settings if necessary for larger file handling (e.g., `max_execution_time`, `memory_limit`).
4. Access `index.php` via your browser and input the remote file URL.

---

## Usage

- Enter the remote file URL.
- (Optional) Specify a custom filename.
- Review file info (type, size, resumable support) and confirm.
- The file will download directly to your server inside the `downloads/` folder.
- After completion, a link to the saved file is provided.

---

## Requirements

- PHP 7.0+ with cURL extension enabled.
- Write permissions for the `downloads/` directory.
- Sufficient disk space for storing downloaded files.

---

## Notes

- This script is designed primarily for downloading large files (e.g., zip archives).
- Server-side PHP and webserver limits (execution time, memory) can still affect downloads; increase those limits if needed.
- For shared hosting, confirm allowed maximum file sizes and PHP execution settings.
- Downloads may take time depending on server bandwidth and remote server response.

---

## Security

- Input URLs are sanitized but use with caution.
- Do not expose this tool publicly without proper access control.
- Validate and verify remote URLs before downloading.

---

## License

This project is licensed under the [MIT License](LICENSE).

---

## Contact & Source

Developed and maintained by Max Base (Seyyed Ali Mohammadiyeh).  
GitHub Repository: [https://github.com/BaseMax/RemoteDownloaderPHP](https://github.com/BaseMax/RemoteDownloaderPHP)  
Feel free to submit issues or pull requests.

---

Happy downloading! 🚀
