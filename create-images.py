#!/usr/bin/env python3
"""
Create real PNG and ICO image files for iDEAL-Q system
"""

import struct
import zlib

def create_png(width, height, r, g, b, filename):
    """Create a simple PNG file with solid color"""
    
    def png_chunk(chunk_type, data):
        chunk_data = chunk_type + data
        crc = zlib.crc32(chunk_data) & 0xffffffff
        return struct.pack(">I", len(data)) + chunk_data + struct.pack(">I", crc)
    
    # PNG signature
    png_signature = b'\x89PNG\r\n\x1a\n'
    
    # IHDR chunk
    ihdr_data = struct.pack(">IIBBBBB", width, height, 8, 2, 0, 0, 0)
    ihdr = png_chunk(b'IHDR', ihdr_data)
    
    # IDAT chunk - image data
    raw_data = b''
    for y in range(height):
        raw_data += b'\x00'  # filter type
        for x in range(width):
            raw_data += bytes([r, g, b])
    
    compressed_data = zlib.compress(raw_data, 9)
    idat = png_chunk(b'IDAT', compressed_data)
    
    # IEND chunk
    iend = png_chunk(b'IEND', b'')
    
    # Write PNG file
    with open(filename, 'wb') as f:
        f.write(png_signature + ihdr + idat + iend)
    
    print(f"Created: {filename} ({width}x{height})")

def create_ico(filename):
    """Create a simple ICO file (16x16 blue icon)"""
    # ICO header
    ico_header = struct.pack('<HHH', 0, 1, 1)  # reserved, type, count
    
    # ICO directory entry (16x16, 24bit)
    width = 16
    height = 16
    bpp = 24
    
    # BMP data for ICO
    bmp_info_header = struct.pack('<IIIHHIIIIII',
        40,  # header size
        width,
        height * 2,  # height * 2 for ICO
        1,  # planes
        bpp,  # bits per pixel
        0,  # compression
        0,  # image size (can be 0 for uncompressed)
        0, 0, 0, 0  # colors
    )
    
    # Create image data (blue color: RGB = 52, 152, 219)
    image_data = b''
    row_size = ((width * 3 + 3) // 4) * 4  # must be multiple of 4
    for y in range(height):
        row = b''
        for x in range(width):
            row += bytes([219, 152, 52])  # BGR format
        # Pad row to multiple of 4 bytes
        row += b'\x00' * (row_size - len(row))
        image_data += row
    
    # AND mask (all transparent)
    and_mask = b'\x00' * ((width + 31) // 32 * 4 * height)
    
    bmp_data = bmp_info_header + image_data + and_mask
    
    # ICO directory entry
    ico_dir = struct.pack('<BBBBHHII',
        width if width < 256 else 0,
        height if height < 256 else 0,
        0,  # color palette
        0,  # reserved
        1,  # color planes
        bpp,  # bits per pixel
        len(bmp_data),  # size of BMP data
        22  # offset to BMP data (6 + 16)
    )
    
    # Write ICO file
    with open(filename, 'wb') as f:
        f.write(ico_header + ico_dir + bmp_data)
    
    print(f"Created: {filename}")

# Create images
print("Creating PNG and ICO files...")
print()

# System logos (blue gradient color)
create_png(200, 80, 52, 152, 219, 'beaa/files/logos/systemlogo-md.png')
create_png(300, 100, 52, 152, 219, 'files/logos/systemlogo.png')
create_png(40, 40, 52, 152, 219, 'beaa/files/logos/ideal-q-small.png')

# Admin icon
create_png(32, 32, 41, 128, 185, 'beaa/files/shortcut_icons/admin.png')

# ICO file
create_ico('beaa/files/logos/logo-ideal.ico')

# Solo logo
create_png(16, 16, 52, 152, 219, 'logo-solo-16.png')

print()
print("✅ All images created successfully!")
print()
print("Created files:")
print("  - beaa/files/logos/systemlogo-md.png (200x80)")
print("  - files/logos/systemlogo.png (300x100)")
print("  - beaa/files/logos/ideal-q-small.png (40x40)")
print("  - beaa/files/shortcut_icons/admin.png (32x32)")
print("  - beaa/files/logos/logo-ideal.ico (16x16)")
print("  - logo-solo-16.png (16x16)")
