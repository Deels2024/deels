<?php

$this->ident_ref_table = array(

	array(
		0,
		NULL,
		'h',
		'377abcaf271c',
		0,
		NULL,
		's',
		'',
		'7z',
		array(
			'7z'
		),
		'application/x-7z-compressed',
		'org.7-zip.7-zip-archive',
		'7Zip format',
		NULL,
		NULL,
		'fmt/484',
		NULL,
		'Aggregate',
		NULL,
		'7z is a compressed archive file format that supports several different data compression, encryption and pre-processing algorithms. The 7z format initially appeared as implemented by the 7-Zip archiver. The 7-Zip program is publicly available under the terms of the GNU Lesser General Public License.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1271&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/7z',
			'https://www.7-zip.org/7z.html'
		)
	),

	array(
		0,
		NULL,
		'h',
		'28b52ffd',
		0,
		NULL,
		's',
		'',
		'zst',
		array(
			'zst'
		),
		'application/zstd',
		NULL,
		'Zstandard Compression',
		NULL,
		NULL,
		NULL,
		NULL,
		'Aggregate',
		NULL,
		'Zstandard is a fast compression algorithm, providing high compression ratios. It also offers a special mode for small data, called dictionary compression. The reference library offers a very wide range of speed / compression trade-off, and is backed by an extremely fast decoder (see benchmarks below). Zstandard library is provided as open source software using a BSD license. Its format is stable and published as IETF RFC 8878.',
		NULL,
		NULL,
		array(
			'https://www.rfc-editor.org/rfc/rfc8878',
			'https://en.wikipedia.org/wiki/Zstd',
			'https://facebook.github.io/zstd/',
			'https://youtu.be/k5XsiuxHv_A',
			'https://github.com/facebook/zstd'
		)
	),

	array(
		0,
		41,
		'sr',
		// BOF Absolute from beginning of file, magic bytes: .snd|dns.{20}AudacityBlockFile
		'(?:\\x2e\\x73\\x6e\\x64|\\x64\\x6e\\x73\\x2e)[\\x00-\\xff]{20}\\x41\\x75\\x64\\x61\\x63\\x69\\x74\\x79\\x42\\x6c\\x6f\\x63\\x6b\\x46\\x69\\x6c\\x65',
		0,
		NULL,
		's',
		'',
		'au',
		array(
			'au'
		),
		NULL,
		NULL,
		'Audacity Audio Block File',
		NULL,
		NULL,
		'fmt/1822',
		NULL,
		'Audio',
		NULL,
		'Audacity is free and open source audio editing, recording and post-processing software. The first version (0.8) was released in 2000 by Dominic Mazzoni and Roger Dannenberg at Carnegie Mellon University, Pennsylvania. It remains available today. An Audacity project consists of an aup project file, which is the name of the project followed by .aup . Next to the .aup file is a folder containing many .au files, each one being a segment of audio data. The aup file describes how audacity links the au files together to form the tracks in the project.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=2673&strPageToDisplay=summary'
		)
	),

	array(
		0,
		24,
		'sr',
		// Header comprises 6 4-byte elements: Magic number (.snd); header length; data length; encoding; sample rate; channels.
		'\\x2e\\x73\\x6e\\x64\\x00\\x00\\x00[\\x00-\\xff]{5}\\x00\\x00\\x00[\\x01-\\x07\\x17-\\x1b][\\x00-\\xff]{4}\\x00\\x00\\x00[\\x00-\\xff]',
		0,
		NULL,
		's',
		'',
		'au',
		array(
			'au',
			'snd'
		),
		'audio/basic',
		NULL,
		'NeXT/Sun sound',
		NULL,
		NULL,
		'x-fmt/139',
		NULL,
		'Audio',
		NULL,
		'This is an outline record only, and requires further details, research or authentication to provide information that will enable users to further understand the format and to assess digital preservation risks associated with it if appropriate. If you are able to help by supplying any additional information concerning this entry, please return to the main PRONOM page and select ‘Add an Entry’.',
		'Text',
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=199&strPageToDisplay=summary',
			'https://www.file-recovery.com/au-signature-format.htm',
			'https://en.wikipedia.org/wiki/Au_file_format',
			'https://en.wikipedia.org/wiki/List_of_file_signatures'
		)
	),

	array(
		0,
		12,
		'sr',
		'\\x52\\x49\\x46\\x46[\\x00-\\xff]{4}\\x41\\x56\\x49\\x20',
		0,
		NULL,
		's',
		'',
		'avi',
		array(
			'avi'
		),
		'video/x-msvideo',
		'public.avi',
		'Audio/Video Interleaved Format',
		NULL,
		'AVI (Generic)',
		'fmt/5',
		NULL,
		'Audio, Video',
		'Full',
		'Audio Video Interleave (also Audio Video Interleaved), known by its initials AVI and the .avi filename extension is a multimedia container format introduced by Microsoft in November 1992 as part of its Video for Windows software. AVI files can contain both audio and video data in a file container that allows synchronous audio-with-video playback. Like the DVD video format, AVI files support multiple streaming audio and video, although these features are seldom used.',
		'Binary',
		'Little-endian (Intel)',
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=655&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/Audio_Video_Interleave',
			'https://docs.microsoft.com/en-us/previous-versions//ms779636(v=vs.85)',
			'https://docs.microsoft.com/en-us/previous-versions//ms783421(v=vs.85)',
			'http://www.alexander-noe.com/video/documentation/avi.pdf'
		)
	),

	array(
		0,
		10,
		'sr',
		'\\x00\\x00[\\x00-\\xff]{6}\\x01[\\x01\\x04\\x08]',
		0,
		NULL,
		's',
		'',
		'bmp',
		array(
			'bmp',
			'ddb'
		),
		'image/bmp',
		'com.microsoft.bmp',
		'Windows Bitmap',
		'1.0',
		'BMP (1.0)',
		'fmt/114',
		NULL,
		'Image (Raster)',
		NULL,
		'The BMP file format, also known as bitmap image file, device independent bitmap (DIB) file format and bitmap, is a raster graphics image file format used to store bitmap digital images, independently of the display device (such as a graphics adapter), especially on Microsoft Windows and OS/2 operating systems.',
		'Binary',
		'Little-endian (Intel)',
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=727&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/BMP_file_format',
			'https://medium.com/sysf/bits-to-bitmaps-a-simple-walkthrough-of-bmp-image-format-765dc6857393'
		)
	),

	array(
		0,
		26,
		'sr',
		'\\x42\\x4d[\\x00-\\xff]{4}\\x00\\x00\\x00\\x00[\\x00-\\xff]{4}\\x0c\\x00\\x00\\x00[\\x00-\\xff]{4}\\x01\\x00[\\x01\\x04\\x08\\x18]\\x00',
		0,
		NULL,
		's',
		'',
		'bmp',
		array(
			'bmp',
			'dib'
		),
		'image/bmp',
		'com.microsoft.bmp',
		'Windows Bitmap',
		'2.0',
		'BMP (2.0)',
		'fmt/115',
		NULL,
		'Image (Raster)',
		NULL,
		'The BMP file format, also known as bitmap image file, device independent bitmap (DIB) file format and bitmap, is a raster graphics image file format used to store bitmap digital images, independently of the display device (such as a graphics adapter), especially on Microsoft Windows and OS/2 operating systems.',
		'Binary',
		'Little-endian (Intel)',
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=728&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/BMP_file_format',
			'https://medium.com/sysf/bits-to-bitmaps-a-simple-walkthrough-of-bmp-image-format-765dc6857393'
		)
	),

	array(
		0,
		34,
		'sr',
		'\\x42\\x4d[\\x00-\\xff]{4}\\x00\\x00\\x00\\x00[\\x00-\\xff]{4}\\x28\\x00\\x00\\x00[\\x00-\\xff]{8}\\x01\\x00[\\x01\\x04\\x08\\x18\\x20]\\x00[\\x00\\x01\\x02]\\x00\\x00\\x00',
		0,
		NULL,
		's',
		'',
		'bmp',
		array(
			'bmp',
			'dib'
		),
		'image/bmp',
		'com.microsoft.bmp',
		'Windows Bitmap',
		'3.0',
		'BMP (3.0)',
		'fmt/116',
		NULL,
		'Image (Raster)',
		NULL,
		'The BMP file format, also known as bitmap image file, device independent bitmap (DIB) file format and bitmap, is a raster graphics image file format used to store bitmap digital images, independently of the display device (such as a graphics adapter), especially on Microsoft Windows and OS/2 operating systems.',
		'Binary',
		'Little-endian (Intel)',
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=729&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/BMP_file_format',
			'https://medium.com/sysf/bits-to-bitmaps-a-simple-walkthrough-of-bmp-image-format-765dc6857393'
		)
	),

	array(
		0,
		34,
		'sr',
		'\\x42\\x4d[\\x00-\\xff]{4}\\x00\\x00\\x00\\x00[\\x00-\\xff]{4}\\x28\\x00\\x00\\x00[\\x00-\\xff]{8}\\x01\\x00[\\x10\\x20]\\x00\\x03\\x00\\x00\\x00',
		0,
		NULL,
		's',
		'',
		'bmp',
		array(
			'bmp',
			'dib'
		),
		'image/bmp',
		'com.microsoft.bmp',
		'Windows Bitmap',
		'3.0 NT',
		'BMP (3.0 NT)',
		'fmt/117',
		NULL,
		'Image (Raster)',
		NULL,
		'The BMP file format, also known as bitmap image file, device independent bitmap (DIB) file format and bitmap, is a raster graphics image file format used to store bitmap digital images, independently of the display device (such as a graphics adapter), especially on Microsoft Windows and OS/2 operating systems.',
		'Binary',
		'Little-endian (Intel)',
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=730&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/BMP_file_format',
			'https://medium.com/sysf/bits-to-bitmaps-a-simple-walkthrough-of-bmp-image-format-765dc6857393'
		)
	),

	array(
		0,
		35,
		'sr',
		'\\x42\\x4d[\\x00-\\xff]{4}\\x00\\x00\\x00\\x00[\\x00-\\xff]{4}\\x6c\\x00\\x00\\x00[\\x00-\\xff]{8}\\x01\\x00[\\x01\\x04\\x08\\x10\\x18\\x20]\\x00[\\x00\\x01\\x02\\x03]\\x00\\x00\\x00\\x00',
		0,
		NULL,
		's',
		'',
		'bmp',
		array(
			'bmp',
			'dib'
		),
		'image/bmp',
		'com.microsoft.bmp',
		'Windows Bitmap',
		'4.0',
		'BMP (4.0)',
		'fmt/118',
		NULL,
		'Image (Raster)',
		NULL,
		'The BMP file format, also known as bitmap image file, device independent bitmap (DIB) file format and bitmap, is a raster graphics image file format used to store bitmap digital images, independently of the display device (such as a graphics adapter), especially on Microsoft Windows and OS/2 operating systems.',
		'Binary',
		'Little-endian (Intel)',
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=731&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/BMP_file_format',
			'https://medium.com/sysf/bits-to-bitmaps-a-simple-walkthrough-of-bmp-image-format-765dc6857393'
		)
	),

	array(
		0,
		35,
		'sr',
		'\\x42\\x4d[\\x00-\\xff]{4}\\x00\\x00\\x00\\x00[\\x00-\\xff]{4}\\x7c\\x00\\x00\\x00[\\x00-\\xff]{8}\\x01\\x00[\\x01\\x04\\x08\\x10\\x18\\x20]\\x00[\\x00\\x01\\x02\\x03\\x04\\x05]\\x00\\x00\\x00\\x00',
		0,
		NULL,
		's',
		'',
		'bmp',
		array(
			'bmp',
			'dib'
		),
		'image/bmp',
		'com.microsoft.bmp',
		'Windows Bitmap',
		'5.0',
		'BMP (5.0)',
		'fmt/119',
		NULL,
		'Image (Raster)',
		NULL,
		'The BMP file format, also known as bitmap image file, device independent bitmap (DIB) file format and bitmap, is a raster graphics image file format used to store bitmap digital images, independently of the display device (such as a graphics adapter), especially on Microsoft Windows and OS/2 operating systems.',
		'Binary',
		'Little-endian (Intel)',
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=732&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/BMP_file_format',
			'https://medium.com/sysf/bits-to-bitmaps-a-simple-walkthrough-of-bmp-image-format-765dc6857393'
		)
	),

	array(
		0,
		28,
		'sr',
		'\\x42\\x4d[\\x00-\\xff]{12}[\\x10\\x30\\x40]\\x00\\x00\\x00[\\x00-\\xff]{8}\\x01\\x00',
		0,
		NULL,
		's',
		'',
		'bmp',
		array(
			'bmp'
		),
		'image/bmp',
		NULL,
		'OS/2 Bitmap',
		'2.0',
		NULL,
		'x-fmt/270',
		NULL,
		'Image (Raster)',
		NULL,
		'The IBM OS/2 Bitmap (BMP) file format is one of several graphics file formats supported by the OS/2 operating system. BMP is the native bitmap format of OS/2 and is used to store several types of bitmap data, including icons and pointers. Most graphics and imaging applications operating under OS/2 support the creation and display of BMP format files. BMP is also found in MS-DOS and Microsoft Windows and originated in that environment.',
		'Text',
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=402&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/BMP_file_format',
			'https://www.fileformat.info/format/os2bmp/egff.htm'
		)
	),

	array(
		0,
		34,
		'sr',
		'\\x42\\x4d[\\x00-\\xff]{12}\\x28\\x00\\x00\\x00[\\x00-\\xff]{8}\\x01\\x00\\x18\\x00\\x04\\x00\\x00\\x00',
		0,
		NULL,
		's',
		'',
		'bmp',
		array(
			'bmp'
		),
		'image/bmp',
		NULL,
		'OS/2 Bitmap',
		'2.0',
		NULL,
		'x-fmt/270',
		NULL,
		'Image (Raster)',
		NULL,
		'The IBM OS/2 Bitmap (BMP) file format is one of several graphics file formats supported by the OS/2 operating system. BMP is the native bitmap format of OS/2 and is used to store several types of bitmap data, including icons and pointers. Most graphics and imaging applications operating under OS/2 support the creation and display of BMP format files. BMP is also found in MS-DOS and Microsoft Windows and originated in that environment.',
		'Text',
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=402&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/BMP_file_format',
			'https://www.fileformat.info/format/os2bmp/egff.htm'
		)
	),

	array(
		0,
		34,
		'sr',
		'\\x42\\x4d[\\x00-\\xff]{12}\\x28\\x00\\x00\\x00[\\x00-\\xff]{8}\\x01\\x00\\x01\\x00\\x03\\x00\\x00\\x00',
		0,
		NULL,
		's',
		'',
		'bmp',
		array(
			'bmp'
		),
		'image/bmp',
		NULL,
		'OS/2 Bitmap',
		'2.0',
		NULL,
		'x-fmt/270',
		NULL,
		'Image (Raster)',
		NULL,
		'The IBM OS/2 Bitmap (BMP) file format is one of several graphics file formats supported by the OS/2 operating system. BMP is the native bitmap format of OS/2 and is used to store several types of bitmap data, including icons and pointers. Most graphics and imaging applications operating under OS/2 support the creation and display of BMP format files. BMP is also found in MS-DOS and Microsoft Windows and originated in that environment.',
		'Text',
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=402&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/BMP_file_format',
			'https://www.fileformat.info/format/os2bmp/egff.htm'
		)
	),

	array(
		0,
		10,
		'sr',
		'\\x42\\x5a\\x68[\\x00-\\xff]\\x31\\x41\\x59\\x26\\x53\\x59',
		0,
		NULL,
		's',
		'',
		'bz2',
		array(
			'bz2'
		),
		'application/x-bzip',
		'public.archive.bzip2',
		'BZIP2 Compressed Archive',
		NULL,
		NULL,
		'x-fmt/268',
		NULL,
		'Aggregate',
		NULL,
		'bzip2 is a free and open-source file compression program that uses the Burrows–Wheeler algorithm. It only compresses single files and is not a file archiver. It is developed by Julian Seward and maintained by Federico Mena. Seward made the first public release of bzip2, version 0.15, in July 1996. The compressor\'s stability and popularity grew over the next several years, and Seward released version 1.0 in late 2000.',
		'Text',
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=388&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/Bzip2',
			'https://github.com/dsnet/compress/blob/master/doc/bzip2-format.pdf'
		)
	),

	array(
		0,
		26,
		'sr',
		'\\x4d\\x53\\x43\\x46[\\x00-\\xff]{20}\\x03\\x01',
		0,
		NULL,
		's',
		'',
		'cab',
		array(
			'cab'
		),
		'application/vnd.ms-cab-compressed',
		'public.archive.cab',
		'Windows Cabinet File',
		NULL,
		NULL,
		'x-fmt/414',
		NULL,
		NULL,
		NULL,
		'Cabinet (or CAB) is an archive-file format for Microsoft Windows that supports lossless data compression and embedded digital certificates used for maintaining archive integrity. Cabinet files have .cab filename extensions and are recognized by their first 4 bytes MSCF. Cabinet files were known originally as Diamond files.',
		'Text',
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=801&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/Cabinet_(file_format)'
		)
	),

	array(
		0,
		NULL,
		's',
		'ITSF',
		0,
		NULL,
		's',
		'',
		'chm',
		array(
			'chi',
			'chm',
			'chw'
		),
		'application/vnd.ms-htmlhelp',
		NULL,
		'Microsoft Compiled HTML Help',
		NULL,
		NULL,
		'fmt/634',
		NULL,
		'Text (Structured)',
		NULL,
		'Microsoft Compiled HTML Help is a Microsoft proprietary online help format, consisting of a collection of HTML pages, an index and other navigation tools. The files are compressed and deployed in a binary format with the extension .CHM, for Compiled HTML. The format is often used for software documentation.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1433&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/Microsoft_Compiled_HTML_Help',
			'http://www.russotto.net/chm/chmformat.html',
			'http://www.nongnu.org/chmspec/latest/index.html'
		)
	),

	array(
		0,
		NULL,
		'h',
		'00000200',
		0,
		NULL,
		's',
		'',
		'cur',
		array(
			'cur'
		),
		'image/x-win-bitmap',
		'com.microsoft.cur',
		'Microsoft Windows Cursor',
		NULL,
		NULL,
		'fmt/385',
		NULL,
		'Image (Raster)',
		NULL,
		'The CUR file format is an almost identical image file format for non-animated cursors in Microsoft Windows. The only differences between these two file formats are the bytes used to identify them and the addition of a hotspot in the CUR format header; the hotspot is defined as the pixel offset (in x,y coordinates) from the top-left corner of the cursor image where the user is actually pointing the mouse.',
		'Text',
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1133&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/ICO_(file_format)'
		)
	),

	array(
		0,
		NULL,
		'h',
		'00000100',
		0,
		NULL,
		's',
		'',
		'ico',
		array(
			'ico'
		),
		'image/x-icon',
		'com.microsoft.ico',
		'Icon file format',
		NULL,
		NULL,
		'x-fmt/418',
		NULL,
		'Image (Raster)',
		NULL,
		'The ICO file format is an image file format for computer icons in Microsoft Windows. ICO files contain one or more small images at multiple sizes and color depths, such that they may be scaled appropriately. In Windows, all executables that display an icon to the user, on the desktop, in the Start Menu, or in Windows Explorer, must carry the icon in ICO format.',
		'Text',
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=805&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/ICO_(file_format)'
		)
	),

	array(
		0,
		20,
		'sr',
		'\\x41\\x54\\x26\\x54\\x46\\x4f\\x52\\x4d[\\x00-\\xff]{4}(?:\\x44\\x4A\\x56\\x4D\\x44\\x49\\x52\\x4D|\\x44\\x4A\\x56\\x55\\x49\\x4E\\x46\\x4F)',
		0,
		NULL,
		's',
		'',
		'djv',
		array(
			'djv',
			'djvu'
		),
		'image/vnd.djvu',
		NULL,
		'DjVu File Format',
		NULL,
		NULL,
		'fmt/255',
		NULL,
		'Image (Raster)',
		NULL,
		'DjVu is a computer file format designed primarily to store scanned documents, especially those containing a combination of text, line drawings, indexed color images, and photographs. It uses technologies such as image layer separation of text and background/images, progressive loading, arithmetic coding, and lossy compression for bitonal (monochrome) images. This allows high-quality, readable images to be stored in a minimum of space, so that they can be made available on the web.',
		'Text',
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=993&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/DjVu'
		)
	),

	array(
		0,
		NULL,
		'h',
		'feedface',
		0,
		NULL,
		's',
		'',
		'',
		array(
			'',
			'bundle',
			'dylib',
			'o'
		),
		'application/octet-stream',
		'com.apple.mach-o-binary',
		'Mach-O',
		'32bit 1',
		NULL,
		'fmt/692',
		NULL,
		NULL,
		NULL,
		'Mach-O, short for Mach object file format, is a file format for executables, object code, shared libraries, dynamically-loaded code, and core dumps. A replacement for the a.out format, Mach-O offers more extensibility and faster access to information in the symbol table. Mach-O is used by most systems based on the Mach kernel. NeXTSTEP, macOS, and iOS are examples of systems that use this format for native executables, libraries and object code.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1491&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/Mach-O'
		)
	),

	array(
		0,
		NULL,
		'h',
		'cefaedfe',
		0,
		NULL,
		's',
		'',
		'',
		array(
			'',
			'bundle',
			'dylib',
			'o'
		),
		'application/octet-stream',
		'com.apple.mach-o-binary',
		'Mach-O',
		'32bit 2',
		NULL,
		'fmt/692',
		NULL,
		NULL,
		NULL,
		'Mach-O, short for Mach object file format, is a file format for executables, object code, shared libraries, dynamically-loaded code, and core dumps. A replacement for the a.out format, Mach-O offers more extensibility and faster access to information in the symbol table. Mach-O is used by most systems based on the Mach kernel. NeXTSTEP, macOS, and iOS are examples of systems that use this format for native executables, libraries and object code.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1491&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/Mach-O'
		)
	),

	array(
		0,
		NULL,
		'h',
		'feedfacf',
		0,
		NULL,
		's',
		'',
		'',
		array(
			'',
			'bundle',
			'dylib',
			'o'
		),
		'application/octet-stream',
		'com.apple.mach-o-binary',
		'Mach-O',
		'64bit 1',
		NULL,
		'fmt/693',
		NULL,
		NULL,
		NULL,
		'Mach-O, short for Mach object file format, is a file format for executables, object code, shared libraries, dynamically-loaded code, and core dumps. A replacement for the a.out format, Mach-O offers more extensibility and faster access to information in the symbol table. Mach-O is used by most systems based on the Mach kernel. NeXTSTEP, macOS, and iOS are examples of systems that use this format for native executables, libraries and object code.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1492&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/Mach-O'
		)
	),

	array(
		0,
		NULL,
		'h',
		'cffaedfe',
		0,
		NULL,
		's',
		'',
		'',
		array(
			'',
			'bundle',
			'dylib',
			'o'
		),
		'application/octet-stream',
		'com.apple.mach-o-binary',
		'Mach-O',
		'64bit 2',
		NULL,
		'fmt/693',
		NULL,
		NULL,
		NULL,
		'Mach-O, short for Mach object file format, is a file format for executables, object code, shared libraries, dynamically-loaded code, and core dumps. A replacement for the a.out format, Mach-O offers more extensibility and faster access to information in the symbol table. Mach-O is used by most systems based on the Mach kernel. NeXTSTEP, macOS, and iOS are examples of systems that use this format for native executables, libraries and object code.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1492&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/Mach-O'
		)
	),

	array(
		0,
		NULL,
		'h',
		'7f454c46010201',
		0,
		NULL,
		's',
		'',
		'',
		array(
			'',
			'axf',
			'bin',
			'elf',
			'ko',
			'mod',
			'o',
			'prx',
			'puff',
			'so'
		),
		'application/octet-stream',
		NULL,
		'Executable and Linkable Format',
		'32bit Big Endian',
		NULL,
		'fmt/689',
		NULL,
		NULL,
		NULL,
		'In computing, the Executable and Linkable Format (ELF, formerly named Extensible Linking Format), is a common standard file format for executable files, object code, shared libraries, and core dumps. First published in the specification for the application binary interface (ABI) of the Unix operating system version named System V Release 4 (SVR4), and later in the Tool Interface Standard, it was quickly accepted among different vendors of Unix systems. In 1999, it was chosen as the standard binary file format for Unix and Unix-like systems on x86 processors by the 86open project.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1488&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/Executable_and_Linkable_Format',
			'https://refspecs.linuxfoundation.org/LSB_4.1.0/LSB-Core-AMD64/LSB-Core-AMD64/elf-amd64.html',
			'http://www.sco.com/developers/gabi/2000-07-17/ch4.eheader.html'
		)
	),

	array(
		0,
		NULL,
		'h',
		'7f454c46010101',
		0,
		NULL,
		's',
		'',
		'',
		array(
			'',
			'axf',
			'bin',
			'elf',
			'ko',
			'mod',
			'o',
			'prx',
			'puff',
			'so'
		),
		'application/octet-stream',
		NULL,
		'Executable and Linkable Format',
		'32bit Little Endian',
		NULL,
		'fmt/688',
		NULL,
		NULL,
		NULL,
		'In computing, the Executable and Linkable Format (ELF, formerly named Extensible Linking Format), is a common standard file format for executable files, object code, shared libraries, and core dumps. First published in the specification for the application binary interface (ABI) of the Unix operating system version named System V Release 4 (SVR4), and later in the Tool Interface Standard, it was quickly accepted among different vendors of Unix systems. In 1999, it was chosen as the standard binary file format for Unix and Unix-like systems on x86 processors by the 86open project.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1487&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/Executable_and_Linkable_Format',
			'https://refspecs.linuxfoundation.org/LSB_4.1.0/LSB-Core-AMD64/LSB-Core-AMD64/elf-amd64.html',
			'http://www.sco.com/developers/gabi/2000-07-17/ch4.eheader.html'
		)
	),

	array(
		0,
		NULL,
		'h',
		'7f454c46020201',
		0,
		NULL,
		's',
		'',
		'',
		array(
			'',
			'axf',
			'bin',
			'elf',
			'ko',
			'mod',
			'o',
			'prx',
			'puff',
			'so'
		),
		'application/octet-stream',
		NULL,
		'Executable and Linkable Format',
		'64bit Big Endian',
		NULL,
		'fmt/691',
		NULL,
		NULL,
		NULL,
		'In computing, the Executable and Linkable Format (ELF, formerly named Extensible Linking Format), is a common standard file format for executable files, object code, shared libraries, and core dumps. First published in the specification for the application binary interface (ABI) of the Unix operating system version named System V Release 4 (SVR4), and later in the Tool Interface Standard, it was quickly accepted among different vendors of Unix systems. In 1999, it was chosen as the standard binary file format for Unix and Unix-like systems on x86 processors by the 86open project.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1490&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/Executable_and_Linkable_Format',
			'https://refspecs.linuxfoundation.org/LSB_4.1.0/LSB-Core-AMD64/LSB-Core-AMD64/elf-amd64.html',
			'http://www.sco.com/developers/gabi/2000-07-17/ch4.eheader.html'
		)
	),

	array(
		0,
		NULL,
		'h',
		'7f454c46020101',
		0,
		NULL,
		's',
		'',
		'',
		array(
			'',
			'axf',
			'bin',
			'elf',
			'ko',
			'mod',
			'o',
			'prx',
			'puff',
			'so'
		),
		'application/octet-stream',
		NULL,
		'Executable and Linkable Format',
		'64bit Little Endian',
		NULL,
		'fmt/690',
		NULL,
		NULL,
		NULL,
		'In computing, the Executable and Linkable Format (ELF, formerly named Extensible Linking Format), is a common standard file format for executable files, object code, shared libraries, and core dumps. First published in the specification for the application binary interface (ABI) of the Unix operating system version named System V Release 4 (SVR4), and later in the Tool Interface Standard, it was quickly accepted among different vendors of Unix systems. In 1999, it was chosen as the standard binary file format for Unix and Unix-like systems on x86 processors by the 86open project.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1489&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/Executable_and_Linkable_Format',
			'https://refspecs.linuxfoundation.org/LSB_4.1.0/LSB-Core-AMD64/LSB-Core-AMD64/elf-amd64.html',
			'http://www.sco.com/developers/gabi/2000-07-17/ch4.eheader.html'
		)
	),

	array(
		0,
		NULL,
		's',
		'!<arch>',
		0,
		NULL,
		's',
		'',
		'deb',
		array(
			'deb',
			'udeb'
		),
		array(
			'application/vnd.debian.binary-package',
			'application/x-debian-package',
			'application/x-deb'
		),
		NULL,
		'Debian package',
		NULL,
		NULL,
		NULL,
		NULL,
		NULL,
		NULL,
		'deb is the format, as well as extension of the software package format for the Linux distribution Debian and its derivatives.',
		NULL,
		NULL,
		array(
			'https://en.wikipedia.org/wiki/Deb_(file_format)',
			'https://manpages.debian.org/unstable/dpkg-dev/deb.5.en.html'
		)
	),

	array(
		0,
		NULL,
		'h',
		'664c614300000022',
		0,
		NULL,
		's',
		'',
		'flac',
		array(
			'flac'
		),
		array(
			'audio/x-flac',
			'audio/flac'
		),
		'org.xiph.flac',
		'FLAC (Free Lossless Audio Codec)',
		'1.2.1',
		NULL,
		'fmt/279',
		NULL,
		'Audio',
		NULL,
		'FLAC (Free Lossless Audio Codec) is an audio coding format for lossless compression of digital audio, developed by the Xiph.Org Foundation, and is also the name of the free software project producing the FLAC tools, the reference software package that includes a codec implementation. Digital audio compressed by FLAC\'s algorithm can typically be reduced to between 50 and 70 percent of its original size and decompress to an identical copy of the original audio data. FLAC is an open format with royalty-free licensing and a reference implementation which is free software. FLAC has support for metadata tagging, album cover art, and fast seeking.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1019&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/FLAC',
			'https://xiph.org/flac/format.html'
		)
	),

	array(
		0,
		NULL,
		'h',
		'464c5601',
		0,
		NULL,
		's',
		'',
		'flv',
		array(
			'flv'
		),
		'video/x-flv',
		NULL,
		'Macromedia FLV (Flash Video)',
		'1',
		NULL,
		'x-fmt/382',
		NULL,
		'Video',
		NULL,
		'Flash Video is a container file format used to deliver digital video content (e.g., TV shows, movies, etc.) over the Internet using Adobe Flash Player version 6 and newer. Flash Video content may also be embedded within SWF files. There are two different Flash Video file formats: FLV and F4V. The audio and video data within FLV files are encoded in the same way as SWF files. The F4V file format is based on the ISO base media file format, starting with Flash Player 9 update 3. Both formats are supported in Adobe Flash Player and developed by Adobe Systems. FLV was originally developed by Macromedia. In the early 2000s, Flash Video was the de facto standard for web-based streaming video (over RTMP). Users include Hulu, VEVO, Yahoo! Video, metacafe, Reuters.com, and many other news providers.',
		'Binary',
		'Big-endian (Motorola)',
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=653&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/Flash_Video',
			'http://download.macromedia.com/f4v/video_file_format_spec_v10_1.pdf',
			'https://www.loc.gov/preservation/digital/formats/fdd/fdd000131.shtml'
		)
	),

	array(
		0,
		3,
		'sr',
		'[CFZ]WS',
		0,
		NULL,
		's',
		'',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		NULL,
		NULL,
		NULL,
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'46575301',
		0,
		NULL,
		'h',
		'0000',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'1',
		NULL,
		'fmt/104',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=646&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'46575302',
		0,
		NULL,
		'h',
		'0000',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'2',
		NULL,
		'fmt/105',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=647&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'46575303',
		0,
		NULL,
		'h',
		'0000',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'3',
		NULL,
		'fmt/106',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=648&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'46575304',
		0,
		NULL,
		'h',
		'0000',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'4',
		NULL,
		'fmt/107',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=649&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'46575305',
		0,
		NULL,
		'h',
		'0000',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'5',
		NULL,
		'fmt/108',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=650&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'46575306',
		0,
		NULL,
		'h',
		'0000',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'6',
		NULL,
		'fmt/109',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=651&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'43575306',
		0,
		NULL,
		's',
		'',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'6 (zlib compressed)',
		NULL,
		'fmt/109',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=651&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'46575307',
		0,
		NULL,
		'h',
		'0000',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'7',
		NULL,
		'fmt/110',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=652&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'43575307',
		0,
		NULL,
		's',
		'',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'7 (zlib compressed)',
		NULL,
		'fmt/110',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=652&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'46575308',
		0,
		NULL,
		'h',
		'0000',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'8',
		NULL,
		'fmt/505',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1292&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'43575308',
		0,
		NULL,
		's',
		'',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'8 (zlib compressed)',
		NULL,
		'fmt/505',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1292&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'46575309',
		0,
		NULL,
		'h',
		'0000',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'9',
		NULL,
		'fmt/506',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1293&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'43575309',
		0,
		NULL,
		's',
		'',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'9 (zlib compressed)',
		NULL,
		'fmt/506',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1293&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'4657530a',
		0,
		NULL,
		'h',
		'0000',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'10',
		NULL,
		'fmt/507',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1294&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'4357530a',
		0,
		NULL,
		's',
		'',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'10 (zlib compressed)',
		NULL,
		'fmt/507',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1294&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'4657530b',
		0,
		NULL,
		'h',
		'0000',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'11',
		NULL,
		'fmt/757',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1556&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'4357530b',
		0,
		NULL,
		's',
		'',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'11 (zlib compressed)',
		NULL,
		'fmt/757',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1556&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'4657530c',
		0,
		NULL,
		'h',
		'0000',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'12',
		NULL,
		'fmt/758',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1557&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'4357530c',
		0,
		NULL,
		's',
		'',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'12 (zlib compressed)',
		NULL,
		'fmt/758',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1557&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'4657530d',
		0,
		NULL,
		'h',
		'0000',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'13',
		NULL,
		'fmt/759',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1558&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'4357530d',
		0,
		NULL,
		's',
		'',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'13 (zlib compressed)',
		NULL,
		'fmt/759',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1558&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'5a57530d',
		0,
		NULL,
		's',
		'',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'13 (LZMA compressed)',
		NULL,
		'fmt/759',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1558&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'4657530e',
		0,
		NULL,
		'h',
		'0000',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'14',
		NULL,
		'fmt/760',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1560&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'4357530e',
		0,
		NULL,
		's',
		'',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'14 (zlib compressed)',
		NULL,
		'fmt/760',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1560&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'5a57530e',
		0,
		NULL,
		's',
		'',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'14 (LZMA compressed)',
		NULL,
		'fmt/760',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1560&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'4657530f',
		0,
		NULL,
		'h',
		'0000',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'15',
		NULL,
		'fmt/761',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1560&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'4357530f',
		0,
		NULL,
		's',
		'',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'15 (zlib compressed)',
		NULL,
		'fmt/761',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1560&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'5a57530f',
		0,
		NULL,
		's',
		'',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'15 (LZMA compressed)',
		NULL,
		'fmt/761',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1560&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'46575310',
		0,
		NULL,
		'h',
		'0000',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'16',
		NULL,
		'fmt/762',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1561',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'43575310',
		0,
		NULL,
		's',
		'',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'16 (zlib compressed)',
		NULL,
		'fmt/762',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1561',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'5a575310',
		0,
		NULL,
		's',
		'',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'16 (LZMA compressed)',
		NULL,
		'fmt/762',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1561',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'46575311',
		0,
		NULL,
		'h',
		'0000',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'17',
		NULL,
		'fmt/763',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1562',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'43575311',
		0,
		NULL,
		's',
		'',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'17 (zlib compressed)',
		NULL,
		'fmt/763',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1562',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'5a575311',
		0,
		NULL,
		's',
		'',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'17 (LZMA compressed)',
		NULL,
		'fmt/763',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1562',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'46575312',
		0,
		NULL,
		'h',
		'0000',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'18',
		NULL,
		'fmt/764',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1563',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'43575312',
		0,
		NULL,
		's',
		'',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'18 (zlib compressed)',
		NULL,
		'fmt/764',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1563',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'5a575312',
		0,
		NULL,
		's',
		'',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'18 (LZMA compressed)',
		NULL,
		'fmt/764',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1563',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'46575313',
		0,
		NULL,
		'h',
		'0000',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'19',
		NULL,
		'fmt/765',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1564&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'43575313',
		0,
		NULL,
		's',
		'',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'19 (zlib compressed)',
		NULL,
		'fmt/765',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1564&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'5a575313',
		0,
		NULL,
		's',
		'',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'19 (LZMA compressed)',
		NULL,
		'fmt/765',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1564&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'46575314',
		0,
		NULL,
		'h',
		'0000',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'20',
		NULL,
		'fmt/766',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1565&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'43575314',
		0,
		NULL,
		's',
		'',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'20 (zlib compressed)',
		NULL,
		'fmt/766',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1565&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'5a575314',
		0,
		NULL,
		's',
		'',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'20 (LZMA compressed)',
		NULL,
		'fmt/766',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1565&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'46575315',
		0,
		NULL,
		'h',
		'0000',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'21',
		NULL,
		'fmt/767',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1566&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'43575315',
		0,
		NULL,
		's',
		'',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'21 (zlib compressed)',
		NULL,
		'fmt/767',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1566&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'5a575315',
		0,
		NULL,
		's',
		'',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'21 (LZMA compressed)',
		NULL,
		'fmt/767',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'46575316',
		0,
		NULL,
		'h',
		'0000',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'22',
		NULL,
		'fmt/768',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1567&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'43575316',
		0,
		NULL,
		's',
		'',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'22 (zlib compressed)',
		NULL,
		'fmt/768',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1567&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'5a575316',
		0,
		NULL,
		's',
		'',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'22 (LZMA compressed)',
		NULL,
		'fmt/768',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1567&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'46575317',
		0,
		NULL,
		'h',
		'0000',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'23',
		NULL,
		'fmt/769',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1568&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'43575317',
		0,
		NULL,
		's',
		'',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'23 (zlib compressed)',
		NULL,
		'fmt/769',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1568&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'5a575317',
		0,
		NULL,
		's',
		'',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'23 (LZMA compressed)',
		NULL,
		'fmt/769',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1568&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'46575318',
		0,
		NULL,
		'h',
		'0000',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'24',
		NULL,
		'fmt/770',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1569&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'43575318',
		0,
		NULL,
		's',
		'',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'24 (zlib compressed)',
		NULL,
		'fmt/770',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1569&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'5a575318',
		0,
		NULL,
		's',
		'',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'24 (LZMA compressed)',
		NULL,
		'fmt/770',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1569&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'46575319',
		0,
		NULL,
		'h',
		'0000',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'25',
		NULL,
		'fmt/771',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1570&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'43575319',
		0,
		NULL,
		's',
		'',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'25 (zlib compressed)',
		NULL,
		'fmt/771',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1570&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'5a575319',
		0,
		NULL,
		's',
		'',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'25 (LZMA compressed)',
		NULL,
		'fmt/771',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1570&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'4657531a',
		0,
		NULL,
		'h',
		'0000',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'26',
		NULL,
		'fmt/772',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1571&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'4357531a',
		0,
		NULL,
		's',
		'',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'26 (zlib compressed)',
		NULL,
		'fmt/772',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1571&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'5a57531a',
		0,
		NULL,
		's',
		'',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'26 (LZMA compressed)',
		NULL,
		'fmt/772',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1571&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'4657531b',
		0,
		NULL,
		'h',
		'0000',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'27',
		NULL,
		'fmt/773',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1572&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'4357531b',
		0,
		NULL,
		's',
		'',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'27 (zlib compressed)',
		NULL,
		'fmt/773',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1572&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'5a57531b',
		0,
		NULL,
		's',
		'',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'27 (LZMA compressed)',
		NULL,
		'fmt/773',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1572&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'4657531c',
		0,
		NULL,
		'h',
		'0000',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'28',
		NULL,
		'fmt/774',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1573&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'4357531c',
		0,
		NULL,
		's',
		'',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'28 (zlib compressed)',
		NULL,
		'fmt/774',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1573&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'5a57531c',
		0,
		NULL,
		's',
		'',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'28 (LZMA compressed)',
		NULL,
		'fmt/774',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1573&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'4657531d',
		0,
		NULL,
		'h',
		'0000',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'29',
		NULL,
		'fmt/775',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1574&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'4357531d',
		0,
		NULL,
		's',
		'',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'29 (zlib compressed)',
		NULL,
		'fmt/775',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1574&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'5a57531d',
		0,
		NULL,
		's',
		'',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'29 (LZMA compressed)',
		NULL,
		'fmt/775',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1574&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'4657531e',
		0,
		NULL,
		'h',
		'0000',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'30',
		NULL,
		'fmt/776',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1575&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'4357531e',
		0,
		NULL,
		's',
		'',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'30 (zlib compressed)',
		NULL,
		'fmt/776',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1575&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		NULL,
		'h',
		'5a57531e',
		0,
		NULL,
		's',
		'',
		'swf',
		array(
			'swf'
		),
		'application/x-shockwave-flash',
		NULL,
		'Small Web Format (SWF)',
		'30 (LZMA compressed)',
		NULL,
		'fmt/776',
		NULL,
		NULL,
		NULL,
		'SWF is an Adobe Flash file format used for multimedia, vector graphics and ActionScript. Originating with FutureWave Software, then transferred to Macromedia, and then coming under the control of Adobe, SWF files can contain animations or applets of varying degrees of interactivity and function. They may also occur in programs, commonly browser games, using ActionScript.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1575&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/SWF',
			'https://en.wikipedia.org/wiki/Adobe_Flash'
		)
	),

	array(
		0,
		4,
		'sr',
		'\\x50\\x4b(?:\\x03\\x04|\\x05\\x06|\\x07\\x08)',
		0,
		NULL,
		's',
		'',
		'zip',
		array(
			'zip'
		),
		'application/zip',
		'com.pkware.zip-archive',
		'ZIP Format',
		NULL,
		NULL,
		'x-fmt/263',
		NULL,
		'Aggregate',
		NULL,
		'ZIP is an archive file format that supports lossless data compression. A ZIP file may contain one or more files or directories that may have been compressed. The ZIP file format permits a number of compression algorithms, though DEFLATE is the most common. This format was originally created in 1989 and was first implemented in PKWARE, Inc.\'s PKZIP utility, as a replacement for the previous ARC compression format by Thom Henderson. The ZIP format was then quickly supported by many software utilities other than PKZIP. Microsoft has included built-in ZIP support (under the name "compressed folders") in versions of Microsoft Windows since 1998. Apple has included built-in ZIP support in Mac OS X 10.3 (via BOMArchiveHelper, now Archive Utility) and later. Most free operating systems have built in support for ZIP in similar manners to Windows and Mac OS X.',
		'Text',
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=382&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/Zip_(file_format)',
			'https://pkware.cachefly.net/webdocs/APPNOTE/APPNOTE-5.2.0.txt',
			'https://pkware.cachefly.net/webdocs/APPNOTE/APPNOTE-6.2.0.txt',
			'https://pkware.cachefly.net/webdocs/APPNOTE/APPNOTE-6.3.0.TXT',
			'https://pkware.cachefly.net/webdocs/APPNOTE/APPNOTE-6.3.1.TXT',
			'https://pkware.cachefly.net/webdocs/APPNOTE/APPNOTE-6.3.2.TXT',
			'https://pkware.cachefly.net/webdocs/APPNOTE/APPNOTE-6.3.3.TXT',
			'https://pkware.cachefly.net/webdocs/APPNOTE/APPNOTE-6.3.4.TXT',
			'https://pkware.cachefly.net/webdocs/APPNOTE/APPNOTE-6.3.5.TXT',
			'https://pkware.cachefly.net/webdocs/APPNOTE/APPNOTE-6.3.6.TXT',
			'https://pkware.cachefly.net/webdocs/APPNOTE/APPNOTE-6.3.7.TXT',
			'https://pkware.cachefly.net/webdocs/APPNOTE/APPNOTE-6.3.8.TXT',
			'https://pkware.cachefly.net/webdocs/APPNOTE/APPNOTE-6.3.9.TXT'
		)
	),

	array(
		0,
		58,
		'sr',
		'\\x50\\x4b\\x03\\x04[\\x00-\\xff]{26}\\x6d\\x69\\x6d\\x65\\x74\\x79\\x70\\x65\\x61\\x70\\x70\\x6c\\x69\\x63\\x61\\x74\\x69\\x6f\\x6e\\x2f\\x65\\x70\\x75\\x62\\x2b\\x7a\\x69\\x70',
		0,
		NULL,
		's',
		'',
		'epub',
		array(
			'epub'
		),
		'application/epub+zip',
		NULL,
		'ePub format',
		NULL,
		NULL,
		'fmt/483',
		NULL,
		'Text (Structured)',
		NULL,
		'EPUB is an e-book file format that uses the ".epub" file extension. The term is short for electronic publication and is sometimes styled ePub. EPUB is supported by many e-readers, and compatible software is available for most smartphones, tablets, and computers. EPUB is a technical standard published by the International Digital Publishing Forum (IDPF). It became an official standard of the IDPF in September 2007, superseding the older Open eBook standard.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1270&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/EPUB'
		)
	),

	array(
		0,
		16,
		'sr',
		'\\x52\\x49\\x46\\x46[\\x00-\\xff]{4}\\x57\\x45\\x42\\x50\\x56\\x50\\x38\\x20',
		0,
		NULL,
		's',
		'',
		'webp',
		array(
			'webp'
		),
		'image/webp',
		NULL,
		'WebP',
		'Lossy',
		NULL,
		'fmt/566',
		NULL,
		'Image (Raster)',
		NULL,
		'WebP is an image format employing both lossy and lossless compression. It is currently developed by Google, based on technology acquired with the purchase of On2 Technologies.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1354&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/WebP',
			'https://developers.google.com/speed/webp/faq',
			'https://developers.google.com/speed/webp/docs/compression',
			'https://developers.google.com/speed/webp/docs/riff_container',
			'https://developers.google.com/speed/webp/docs/webp_lossless_bitstream_specification',
			'https://developers.google.com/speed/webp/docs/webp_lossless_alpha_study',
			'https://developers.google.com/speed/webp/docs/webp_study'
		)
	),

	array(
		0,
		16,
		'sr',
		'\\x52\\x49\\x46\\x46[\\x00-\\xff]{4}\\x57\\x45\\x42\\x50\\x56\\x50\\x38\\x4c',
		0,
		NULL,
		's',
		'',
		'webp',
		array(
			'webp'
		),
		'image/webp',
		NULL,
		'WebP',
		'Lossless',
		NULL,
		'fmt/567',
		NULL,
		'Image (Raster)',
		NULL,
		'WebP is an image format employing both lossy and lossless compression. It is currently developed by Google, based on technology acquired with the purchase of On2 Technologies.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1355&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/WebP',
			'https://developers.google.com/speed/webp/faq',
			'https://developers.google.com/speed/webp/docs/compression',
			'https://developers.google.com/speed/webp/docs/riff_container',
			'https://developers.google.com/speed/webp/docs/webp_lossless_bitstream_specification',
			'https://developers.google.com/speed/webp/docs/webp_lossless_alpha_study',
			'https://developers.google.com/speed/webp/docs/webp_study'
		)
	),

	array(
		0,
		16,
		'sr',
		'\\x52\\x49\\x46\\x46[\\x00-\\xff]{4}\\x57\\x45\\x42\\x50\\x56\\x50\\x38\\x58',
		0,
		NULL,
		's',
		'',
		'webp',
		array(
			'webp'
		),
		'image/webp',
		NULL,
		'WebP',
		'Extended',
		NULL,
		'fmt/568',
		NULL,
		'Image (Raster)',
		NULL,
		'WebP is an image format employing both lossy and lossless compression. It is currently developed by Google, based on technology acquired with the purchase of On2 Technologies.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1356&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/WebP',
			'https://developers.google.com/speed/webp/faq',
			'https://developers.google.com/speed/webp/docs/compression',
			'https://developers.google.com/speed/webp/docs/riff_container',
			'https://developers.google.com/speed/webp/docs/webp_lossless_bitstream_specification',
			'https://developers.google.com/speed/webp/docs/webp_lossless_alpha_study',
			'https://developers.google.com/speed/webp/docs/webp_study'
		)
	),

	array(
		0,
		NULL,
		'h',
		'474946383761',
		0,
		NULL,
		's',
		'',
		'gif',
		array(
			'gif'
		),
		'image/gif',
		'com.compuserve.gif',
		'Graphics Interchange Format (GIF)',
		'87a',
		'GIF (1987a)',
		'fmt/3',
		NULL,
		'Image (Raster)',
		'Full',
		'The Graphics Interchange Format (GIF) is a bitmap image format that was developed by a team at the online services provider CompuServe led by American computer scientist Steve Wilhite on June 15, 1987. It has since come into widespread usage on the World Wide Web due to its wide support and portability between applications and operating systems. The format supports up to 8 bits per pixel for each image, allowing a single image to reference its own palette of up to 256 different colors chosen from the 24-bit RGB color space. It also supports animations and allows a separate palette of up to 256 colors for each frame. These palette limitations make GIF less suitable for reproducing color photographs and other images with color gradients, but well-suited for simpler images such as graphics or logos with solid areas of color. Unlike video, the GIF file format does not support audio.',
		'Binary',
		'Little-endian (Intel)',
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=619&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/GIF',
			'https://www.w3.org/Graphics/GIF/spec-gif87.txt'
		)
	),

	array(
		0,
		NULL,
		'h',
		'474946383961',
		0,
		NULL,
		's',
		'',
		'gif',
		array(
			'gif'
		),
		'image/gif',
		'com.compuserve.gif',
		'Graphics Interchange Format (GIF)',
		'89a',
		'GIF (1989a)',
		'fmt/4',
		NULL,
		'Image (Raster)',
		'Full',
		'The Graphics Interchange Format (GIF) is a bitmap image format that was developed by a team at the online services provider CompuServe led by American computer scientist Steve Wilhite on June 15, 1987. It has since come into widespread usage on the World Wide Web due to its wide support and portability between applications and operating systems. The format supports up to 8 bits per pixel for each image, allowing a single image to reference its own palette of up to 256 different colors chosen from the 24-bit RGB color space. It also supports animations and allows a separate palette of up to 256 colors for each frame. These palette limitations make GIF less suitable for reproducing color photographs and other images with color gradients, but well-suited for simpler images such as graphics or logos with solid areas of color. Unlike video, the GIF file format does not support audio.',
		'Binary',
		'Little-endian (Intel)',
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=620&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/GIF',
			'https://www.w3.org/Graphics/GIF/spec-gif89a.txt'
		)
	),

	array(
		0,
		NULL,
		'h',
		'1f8b08',
		0,
		NULL,
		's',
		'',
		'gz',
		array(
			'gz'
		),
		'application/x-gzip',
		'org.gnu.gnu-zip-archive',
		'GZIP Format',
		NULL,
		NULL,
		'x-fmt/266',
		NULL,
		'Aggregate',
		NULL,
		'gzip is a file format and a software application used for file compression and decompression. The program was created by Jean-loup Gailly and Mark Adler as a free software replacement for the compress program used in early Unix systems, and intended for use by GNU (the "g" is from "GNU"). Version 0.1 was first publicly released on 31 October 1992, and version 1.0 followed in February 1993.',
		'Text',
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=386&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/Gzip',
			'https://tools.ietf.org/html/rfc1952.html'
		)
	),

	array(
		0,
		NULL,
		'h',
//		'89504e470d0a1a0a',
		'89504e470d0a1a0a0000000d49484452',
		0,
		NULL,
		'h',
		'0000000049454e44ae426082',
		'png',
		array(
			'png'
		),
		'image/png',
		'public.png',
		'Portable Network Graphics (PNG)',
		NULL,
		NULL,
		NULL,
		NULL,
		'Image (Raster)',
		NULL,
		'Portable Network Graphics (PNG) is a raster-graphics file format that supports lossless data compression. PNG was developed as an improved, non-patented replacement for Graphics Interchange Format (GIF). PNG supports palette-based images (with palettes of 24-bit RGB or 32-bit RGBA colors), grayscale images (with or without alpha channel for transparency), and full-color non-palette-based RGB or RGBA images. The PNG working group designed the format for transferring images on the Internet, not for professional-quality print graphics; therefore non-RGB color spaces such as CMYK are not supported. A PNG file contains a single image in an extensible structure of chunks, encoding the basic pixels and other information such as textual comments and integrity checks documented in RFC 2083.',
		'Binary',
		'Big-endian (Motorola)',
		array(
			'https://en.wikipedia.org/wiki/Portable_Network_Graphics',
			'http://www.libpng.org/pub/png/spec/1.2/PNG-Contents.html',
			'https://www.w3.org/TR/2003/REC-PNG-20031110/'
		)
	),

	array(
		0,
		NULL,
		's',
		'%PDF',
		0,
		1024,
		'sr',
		'\\x25\\x25\\x45\\x4f\\x46[\\x00-\\xff]{0,1019}',
		'pdf',
		array(
			'pdf'
		),
		'application/pdf',
		'com.adobe.pdf',
		'Acrobat PDF - Portable Document Format',
		NULL,
		NULL,
		NULL,
		NULL,
		'Page Description',
		NULL,
		'The Portable Document Format (PDF) is a file format developed by Adobe in 1993 to present documents, including text formatting and images, in a manner independent of application software, hardware, and operating systems. Based on the PostScript language, each PDF file encapsulates a complete description of a fixed-layout flat document, including the text, fonts, vector graphics, raster images and other information needed to display it. PDF was standardized as ISO 32000 in 2008, and no longer requires any royalties for its implementation.',
		NULL,
		NULL,
		array(
			'https://en.wikipedia.org/wiki/PDF',
			'https://www.adobe.com/content/dam/acom/en/devnet/pdf/pdfs/PDFReference13.pdf',
			'https://www.adobe.com/content/dam/acom/en/devnet/pdf/pdf_reference_archive/PDFReference.pdf',
			'https://www.adobe.com/content/dam/acom/en/devnet/pdf/pdf_reference_archive/PDFReference15_v6.pdf',
			'https://www.adobe.com/content/dam/acom/en/devnet/pdf/pdf_reference_archive/PDFReference16.pdf',
			'https://www.adobe.com/content/dam/acom/en/devnet/acrobat/pdfs/pdf_reference_1-7.pdf',
			'https://www.adobe.com/content/dam/acom/en/devnet/acrobat/pdfs/PDF32000_2008.pdf',
			'https://web.archive.org/web/20130902000323if_/http://cdn.parleys.com/p/5148922a0364bc17fc56c6e5/iSUM2012_00_LRO_presentation.pdf',
			'https://web.archive.org/web/20180730100811if_/https://www.immagic.com/eLibrary/ARCHIVES/TECH/ADOBE/A070914X.pdf',
			'http://www.cs.nott.ac.uk/~psadb1/Publications/Download/2002/Hardy02.pdf'
		)
	),

	array(
		0,
		NULL,
		'h',
		'255044462d312e30',
		0,
		1024,
		'sr',
		'\\x25\\x25\\x45\\x4f\\x46[\\x00-\\xff]{0,1019}',
		'pdf',
		array(
			'pdf'
		),
		'application/pdf',
		'com.adobe.pdf',
		'Acrobat PDF 1.0 - Portable Document Format',
		'1.0',
		'PDF (1.0)',
		'fmt/14',
		NULL,
		'Page Description',
		NULL,
		'The Portable Document Format (PDF) is a file format developed by Adobe in 1993 to present documents, including text formatting and images, in a manner independent of application software, hardware, and operating systems. Based on the PostScript language, each PDF file encapsulates a complete description of a fixed-layout flat document, including the text, fonts, vector graphics, raster images and other information needed to display it. PDF was standardized as ISO 32000 in 2008, and no longer requires any royalties for its implementation.',
		'Binary',
		'Big-endian (Motorola)',
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=613&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/PDF',
			'https://www.adobe.com/content/dam/acom/en/devnet/pdf/pdfs/PDFReference13.pdf',
			'https://www.adobe.com/content/dam/acom/en/devnet/pdf/pdf_reference_archive/PDFReference.pdf',
			'https://www.adobe.com/content/dam/acom/en/devnet/pdf/pdf_reference_archive/PDFReference15_v6.pdf',
			'https://www.adobe.com/content/dam/acom/en/devnet/pdf/pdf_reference_archive/PDFReference16.pdf',
			'https://www.adobe.com/content/dam/acom/en/devnet/acrobat/pdfs/pdf_reference_1-7.pdf',
			'https://www.adobe.com/content/dam/acom/en/devnet/acrobat/pdfs/PDF32000_2008.pdf',
			'https://web.archive.org/web/20130902000323if_/http://cdn.parleys.com/p/5148922a0364bc17fc56c6e5/iSUM2012_00_LRO_presentation.pdf',
			'https://web.archive.org/web/20180730100811if_/https://www.immagic.com/eLibrary/ARCHIVES/TECH/ADOBE/A070914X.pdf',
			'http://www.cs.nott.ac.uk/~psadb1/Publications/Download/2002/Hardy02.pdf'
		)
	),

	array(
		0,
		NULL,
		'h',
		'255044462d312e31',
		0,
		1024,
		'sr',
		'\\x25\\x25\\x45\\x4f\\x46[\\x00-\\xff]{0,1019}',
		'pdf',
		array(
			'pdf'
		),
		'application/pdf',
		'com.adobe.pdf',
		'Acrobat PDF 1.1 - Portable Document Format',
		'1.1',
		'PDF (1.1)',
		'fmt/15',
		NULL,
		'Page Description',
		NULL,
		'The Portable Document Format (PDF) is a file format developed by Adobe in 1993 to present documents, including text formatting and images, in a manner independent of application software, hardware, and operating systems. Based on the PostScript language, each PDF file encapsulates a complete description of a fixed-layout flat document, including the text, fonts, vector graphics, raster images and other information needed to display it. PDF was standardized as ISO 32000 in 2008, and no longer requires any royalties for its implementation.',
		'Binary',
		'Big-endian (Motorola)',
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=614&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/PDF',
			'https://www.adobe.com/content/dam/acom/en/devnet/pdf/pdfs/PDFReference13.pdf',
			'https://www.adobe.com/content/dam/acom/en/devnet/pdf/pdf_reference_archive/PDFReference.pdf',
			'https://www.adobe.com/content/dam/acom/en/devnet/pdf/pdf_reference_archive/PDFReference15_v6.pdf',
			'https://www.adobe.com/content/dam/acom/en/devnet/pdf/pdf_reference_archive/PDFReference16.pdf',
			'https://www.adobe.com/content/dam/acom/en/devnet/acrobat/pdfs/pdf_reference_1-7.pdf',
			'https://www.adobe.com/content/dam/acom/en/devnet/acrobat/pdfs/PDF32000_2008.pdf',
			'https://web.archive.org/web/20130902000323if_/http://cdn.parleys.com/p/5148922a0364bc17fc56c6e5/iSUM2012_00_LRO_presentation.pdf',
			'https://web.archive.org/web/20180730100811if_/https://www.immagic.com/eLibrary/ARCHIVES/TECH/ADOBE/A070914X.pdf',
			'http://www.cs.nott.ac.uk/~psadb1/Publications/Download/2002/Hardy02.pdf'
		)
	),

	array(
		0,
		NULL,
		'h',
		'255044462d312e32',
		0,
		1024,
		'sr',
		'\\x25\\x25\\x45\\x4f\\x46[\\x00-\\xff]{0,1019}',
		'pdf',
		array(
			'pdf'
		),
		'application/pdf',
		'com.adobe.pdf',
		'Acrobat PDF 1.2 - Portable Document Format',
		'1.2',
		'PDF (1.2)',
		'fmt/16',
		NULL,
		'Page Description',
		NULL,
		'The Portable Document Format (PDF) is a file format developed by Adobe in 1993 to present documents, including text formatting and images, in a manner independent of application software, hardware, and operating systems. Based on the PostScript language, each PDF file encapsulates a complete description of a fixed-layout flat document, including the text, fonts, vector graphics, raster images and other information needed to display it. PDF was standardized as ISO 32000 in 2008, and no longer requires any royalties for its implementation.',
		'Binary',
		'Big-endian (Motorola)',
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=615&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/PDF',
			'https://www.adobe.com/content/dam/acom/en/devnet/pdf/pdfs/PDFReference13.pdf',
			'https://www.adobe.com/content/dam/acom/en/devnet/pdf/pdf_reference_archive/PDFReference.pdf',
			'https://www.adobe.com/content/dam/acom/en/devnet/pdf/pdf_reference_archive/PDFReference15_v6.pdf',
			'https://www.adobe.com/content/dam/acom/en/devnet/pdf/pdf_reference_archive/PDFReference16.pdf',
			'https://www.adobe.com/content/dam/acom/en/devnet/acrobat/pdfs/pdf_reference_1-7.pdf',
			'https://www.adobe.com/content/dam/acom/en/devnet/acrobat/pdfs/PDF32000_2008.pdf',
			'https://web.archive.org/web/20130902000323if_/http://cdn.parleys.com/p/5148922a0364bc17fc56c6e5/iSUM2012_00_LRO_presentation.pdf',
			'https://web.archive.org/web/20180730100811if_/https://www.immagic.com/eLibrary/ARCHIVES/TECH/ADOBE/A070914X.pdf',
			'http://www.cs.nott.ac.uk/~psadb1/Publications/Download/2002/Hardy02.pdf'
		)
	),

	array(
		0,
		NULL,
		'h',
		'255044462d312e33',
		0,
		1024,
		'sr',
		'\\x25\\x25\\x45\\x4f\\x46[\\x00-\\xff]{0,1019}',
		'pdf',
		array(
			'pdf'
		),
		'application/pdf',
		'com.adobe.pdf',
		'Acrobat PDF 1.3 - Portable Document Format',
		'1.3',
		'PDF (1.3)',
		'fmt/17',
		NULL,
		'Page Description',
		'Full',
		'The Portable Document Format (PDF) is a file format developed by Adobe in 1993 to present documents, including text formatting and images, in a manner independent of application software, hardware, and operating systems. Based on the PostScript language, each PDF file encapsulates a complete description of a fixed-layout flat document, including the text, fonts, vector graphics, raster images and other information needed to display it. PDF was standardized as ISO 32000 in 2008, and no longer requires any royalties for its implementation.',
		'Binary',
		'Big-endian (Motorola)',
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=616&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/PDF',
			'https://www.adobe.com/content/dam/acom/en/devnet/pdf/pdfs/PDFReference13.pdf',
			'https://www.adobe.com/content/dam/acom/en/devnet/pdf/pdf_reference_archive/PDFReference.pdf',
			'https://www.adobe.com/content/dam/acom/en/devnet/pdf/pdf_reference_archive/PDFReference15_v6.pdf',
			'https://www.adobe.com/content/dam/acom/en/devnet/pdf/pdf_reference_archive/PDFReference16.pdf',
			'https://www.adobe.com/content/dam/acom/en/devnet/acrobat/pdfs/pdf_reference_1-7.pdf',
			'https://www.adobe.com/content/dam/acom/en/devnet/acrobat/pdfs/PDF32000_2008.pdf',
			'https://web.archive.org/web/20130902000323if_/http://cdn.parleys.com/p/5148922a0364bc17fc56c6e5/iSUM2012_00_LRO_presentation.pdf',
			'https://web.archive.org/web/20180730100811if_/https://www.immagic.com/eLibrary/ARCHIVES/TECH/ADOBE/A070914X.pdf',
			'http://www.cs.nott.ac.uk/~psadb1/Publications/Download/2002/Hardy02.pdf'
		)
	),

	array(
		0,
		NULL,
		'h',
		'255044462d312e34',
		0,
		1024,
		'sr',
		'\\x25\\x25\\x45\\x4f\\x46[\\x00-\\xff]{0,1019}',
		'pdf',
		array(
			'pdf'
		),
		'application/pdf',
		'com.adobe.pdf',
		'Acrobat PDF 1.4 - Portable Document Format',
		'1.4',
		'PDF (1.4)',
		'fmt/18',
		NULL,
		'Page Description',
		'Full',
		'The Portable Document Format (PDF) is a file format developed by Adobe in 1993 to present documents, including text formatting and images, in a manner independent of application software, hardware, and operating systems. Based on the PostScript language, each PDF file encapsulates a complete description of a fixed-layout flat document, including the text, fonts, vector graphics, raster images and other information needed to display it. PDF was standardized as ISO 32000 in 2008, and no longer requires any royalties for its implementation.',
		'Binary',
		'Big-endian (Motorola)',
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=617&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/PDF',
			'https://www.adobe.com/content/dam/acom/en/devnet/pdf/pdfs/PDFReference13.pdf',
			'https://www.adobe.com/content/dam/acom/en/devnet/pdf/pdf_reference_archive/PDFReference.pdf',
			'https://www.adobe.com/content/dam/acom/en/devnet/pdf/pdf_reference_archive/PDFReference15_v6.pdf',
			'https://www.adobe.com/content/dam/acom/en/devnet/pdf/pdf_reference_archive/PDFReference16.pdf',
			'https://www.adobe.com/content/dam/acom/en/devnet/acrobat/pdfs/pdf_reference_1-7.pdf',
			'https://www.adobe.com/content/dam/acom/en/devnet/acrobat/pdfs/PDF32000_2008.pdf',
			'https://web.archive.org/web/20130902000323if_/http://cdn.parleys.com/p/5148922a0364bc17fc56c6e5/iSUM2012_00_LRO_presentation.pdf',
			'https://web.archive.org/web/20180730100811if_/https://www.immagic.com/eLibrary/ARCHIVES/TECH/ADOBE/A070914X.pdf',
			'http://www.cs.nott.ac.uk/~psadb1/Publications/Download/2002/Hardy02.pdf'
		)
	),

	array(
		0,
		NULL,
		'h',
		'255044462d312e35',
		0,
		1024,
		'sr',
		'\\x25\\x25\\x45\\x4f\\x46[\\x00-\\xff]{0,1019}',
		'pdf',
		array(
			'pdf'
		),
		'application/pdf',
		'com.adobe.pdf',
		'Acrobat PDF 1.5 - Portable Document Format',
		'1.5',
		'PDF (1.5)',
		'fmt/19',
		NULL,
		'Page Description',
		'Full',
		'The Portable Document Format (PDF) is a file format developed by Adobe in 1993 to present documents, including text formatting and images, in a manner independent of application software, hardware, and operating systems. Based on the PostScript language, each PDF file encapsulates a complete description of a fixed-layout flat document, including the text, fonts, vector graphics, raster images and other information needed to display it. PDF was standardized as ISO 32000 in 2008, and no longer requires any royalties for its implementation.',
		'Binary',
		'Big-endian (Motorola)',
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=618&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/PDF',
			'https://www.adobe.com/content/dam/acom/en/devnet/pdf/pdfs/PDFReference13.pdf',
			'https://www.adobe.com/content/dam/acom/en/devnet/pdf/pdf_reference_archive/PDFReference.pdf',
			'https://www.adobe.com/content/dam/acom/en/devnet/pdf/pdf_reference_archive/PDFReference15_v6.pdf',
			'https://www.adobe.com/content/dam/acom/en/devnet/pdf/pdf_reference_archive/PDFReference16.pdf',
			'https://www.adobe.com/content/dam/acom/en/devnet/acrobat/pdfs/pdf_reference_1-7.pdf',
			'https://www.adobe.com/content/dam/acom/en/devnet/acrobat/pdfs/PDF32000_2008.pdf',
			'https://web.archive.org/web/20130902000323if_/http://cdn.parleys.com/p/5148922a0364bc17fc56c6e5/iSUM2012_00_LRO_presentation.pdf',
			'https://web.archive.org/web/20180730100811if_/https://www.immagic.com/eLibrary/ARCHIVES/TECH/ADOBE/A070914X.pdf',
			'http://www.cs.nott.ac.uk/~psadb1/Publications/Download/2002/Hardy02.pdf'
		)
	),

	array(
		0,
		NULL,
		'h',
		'255044462d312e36',
		0,
		1024,
		'sr',
		'\\x25\\x25\\x45\\x4f\\x46[\\x00-\\xff]{0,1019}',
		'pdf',
		array(
			'pdf'
		),
		'application/pdf',
		'com.adobe.pdf',
		'Acrobat PDF 1.6 - Portable Document Format',
		'1.6',
		'PDF (1.6)',
		'fmt/20',
		NULL,
		'Page Description',
		'Full',
		'The Portable Document Format (PDF) is a file format developed by Adobe in 1993 to present documents, including text formatting and images, in a manner independent of application software, hardware, and operating systems. Based on the PostScript language, each PDF file encapsulates a complete description of a fixed-layout flat document, including the text, fonts, vector graphics, raster images and other information needed to display it. PDF was standardized as ISO 32000 in 2008, and no longer requires any royalties for its implementation.',
		'Binary',
		'Big-endian (Motorola)',
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=637&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/PDF',
			'https://www.adobe.com/content/dam/acom/en/devnet/pdf/pdfs/PDFReference13.pdf',
			'https://www.adobe.com/content/dam/acom/en/devnet/pdf/pdf_reference_archive/PDFReference.pdf',
			'https://www.adobe.com/content/dam/acom/en/devnet/pdf/pdf_reference_archive/PDFReference15_v6.pdf',
			'https://www.adobe.com/content/dam/acom/en/devnet/pdf/pdf_reference_archive/PDFReference16.pdf',
			'https://www.adobe.com/content/dam/acom/en/devnet/acrobat/pdfs/pdf_reference_1-7.pdf',
			'https://www.adobe.com/content/dam/acom/en/devnet/acrobat/pdfs/PDF32000_2008.pdf',
			'https://web.archive.org/web/20130902000323if_/http://cdn.parleys.com/p/5148922a0364bc17fc56c6e5/iSUM2012_00_LRO_presentation.pdf',
			'https://web.archive.org/web/20180730100811if_/https://www.immagic.com/eLibrary/ARCHIVES/TECH/ADOBE/A070914X.pdf',
			'http://www.cs.nott.ac.uk/~psadb1/Publications/Download/2002/Hardy02.pdf'
		)
	),

	array(
		0,
		NULL,
		'h',
		'255044462d312e37',
		0,
		1024,
		'sr',
		'\\x25\\x25\\x45\\x4f\\x46[\\x00-\\xff]{0,1019}',
		'pdf',
		array(
			'pdf'
		),
		'application/pdf',
		'com.adobe.pdf',
		'Acrobat PDF 1.7 - Portable Document Format',
		'1.7',
		'PDF (1.7)',
		'fmt/276',
		NULL,
		'Page Description',
		NULL,
		'The Portable Document Format (PDF) is a file format developed by Adobe in 1993 to present documents, including text formatting and images, in a manner independent of application software, hardware, and operating systems. Based on the PostScript language, each PDF file encapsulates a complete description of a fixed-layout flat document, including the text, fonts, vector graphics, raster images and other information needed to display it. PDF was standardized as ISO 32000 in 2008, and no longer requires any royalties for its implementation.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1016&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/PDF',
			'https://www.adobe.com/content/dam/acom/en/devnet/pdf/pdfs/PDFReference13.pdf',
			'https://www.adobe.com/content/dam/acom/en/devnet/pdf/pdf_reference_archive/PDFReference.pdf',
			'https://www.adobe.com/content/dam/acom/en/devnet/pdf/pdf_reference_archive/PDFReference15_v6.pdf',
			'https://www.adobe.com/content/dam/acom/en/devnet/pdf/pdf_reference_archive/PDFReference16.pdf',
			'https://www.adobe.com/content/dam/acom/en/devnet/acrobat/pdfs/pdf_reference_1-7.pdf',
			'https://www.adobe.com/content/dam/acom/en/devnet/acrobat/pdfs/PDF32000_2008.pdf',
			'https://web.archive.org/web/20130902000323if_/http://cdn.parleys.com/p/5148922a0364bc17fc56c6e5/iSUM2012_00_LRO_presentation.pdf',
			'https://web.archive.org/web/20180730100811if_/https://www.immagic.com/eLibrary/ARCHIVES/TECH/ADOBE/A070914X.pdf',
			'http://www.cs.nott.ac.uk/~psadb1/Publications/Download/2002/Hardy02.pdf'
		)
	),

	array(
		0,
		NULL,
		'h',
		'255044462d322e30',
		0,
		1024,
		'sr',
		'\\x25\\x25\\x45\\x4f\\x46[\\x00-\\xff]{0,1019}',
		'pdf',
		array(
			'pdf'
		),
		'application/pdf',
		'com.adobe.pdf',
		'PDF 2.0 - Portable Document Format',
		'2.0',
		NULL,
		'fmt/1129',
		NULL,
		'Page Description',
		NULL,
		'The Portable Document Format (PDF) is a file format developed by Adobe in 1993 to present documents, including text formatting and images, in a manner independent of application software, hardware, and operating systems. Based on the PostScript language, each PDF file encapsulates a complete description of a fixed-layout flat document, including the text, fonts, vector graphics, raster images and other information needed to display it. PDF was standardized as ISO 32000 in 2008, and no longer requires any royalties for its implementation.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1939&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/PDF',
			'https://www.adobe.com/content/dam/acom/en/devnet/pdf/pdfs/PDFReference13.pdf',
			'https://www.adobe.com/content/dam/acom/en/devnet/pdf/pdf_reference_archive/PDFReference.pdf',
			'https://www.adobe.com/content/dam/acom/en/devnet/pdf/pdf_reference_archive/PDFReference15_v6.pdf',
			'https://www.adobe.com/content/dam/acom/en/devnet/pdf/pdf_reference_archive/PDFReference16.pdf',
			'https://www.adobe.com/content/dam/acom/en/devnet/acrobat/pdfs/pdf_reference_1-7.pdf',
			'https://www.adobe.com/content/dam/acom/en/devnet/acrobat/pdfs/PDF32000_2008.pdf',
			'https://web.archive.org/web/20130902000323if_/http://cdn.parleys.com/p/5148922a0364bc17fc56c6e5/iSUM2012_00_LRO_presentation.pdf',
			'https://web.archive.org/web/20180730100811if_/https://www.immagic.com/eLibrary/ARCHIVES/TECH/ADOBE/A070914X.pdf',
			'http://www.cs.nott.ac.uk/~psadb1/Publications/Download/2002/Hardy02.pdf'
		)
	),

	array(
		0,
		NULL,
		's',
		'wOFF',
		0,
		NULL,
		's',
		'',
		'woff',
		array(
			'woff'
		),
		'font/woff',
		NULL,
		'Web Open Font Format',
		'1.0',
		NULL,
		'fmt/616',
		NULL,
		'Font',
		NULL,
		'The Web Open Font Format (WOFF) is a font format for use in web pages. WOFF files are OpenType or TrueType fonts, with format-specific compression applied and additional XML metadata added. The two primary goals are to first distinguish font files intended for use as web fonts from fonts files intended for use in desktop applications via local installation, and second to reduce web font latency when fonts are transferred from a server to a client over a network connection.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1412&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/Web_Open_Font_Format',
			'https://www.w3.org/Submission/WOFF/',
			'https://www.w3.org/TR/WOFF/'
		)
	),

	array(
		0,
		NULL,
		's',
		'wOF2',
		0,
		NULL,
		's',
		'',
		'woff2',
		array(
			'woff2'
		),
		'font/woff2',
		NULL,
		'Web Open Font Format',
		'2.0',
		NULL,
		'fmt/1172',
		NULL,
		'Font',
		NULL,
		'The Web Open Font Format (WOFF) is a font format for use in web pages. WOFF files are OpenType or TrueType fonts, with format-specific compression applied and additional XML metadata added. The two primary goals are to first distinguish font files intended for use as web fonts from fonts files intended for use in desktop applications via local installation, and second to reduce web font latency when fonts are transferred from a server to a client over a network connection.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1982&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/Web_Open_Font_Format',
			'https://www.w3.org/TR/WOFF2/'
		)
	),

	array(
		0,
		2472,
		'sr',
		'[\\x00-\\xff]{12,128}\\x4f\\x53\\x2f\\x32[\\x00-\\xff]{0,256}\\x63\\x6d\\x61\\x70[\\x00-\\xff]{0,256}\\x67\\x6c\\x79\\x66[\\x00-\\xff]{0,256}\\x68\\x65\\x61\\x64[\\x00-\\xff]{0,256}\\x68\\x68\\x65\\x61[\\x00-\\xff]{0,256}\\x68\\x6d\\x74\\x78[\\x00-\\xff]{0,256}\\x6c\\x6f\\x63\\x61[\\x00-\\xff]{0,256}\\x6d\\x61\\x78\\x70[\\x00-\\xff]{0,256}\\x6e\\x61\\x6d\\x65[\\x00-\\xff]{0,256}\\x70\\x6f\\x73\\x74',
		0,
		NULL,
		's',
		'',
		'ttf',
		array(
			'ttf'
		),
		'font/ttf',
		'public.truetype-ttf-font',
		'TrueType Font (TTF)',
		NULL,
		NULL,
		'x-fmt/453',
		NULL,
		'Font',
		'Full',
		'TrueType is an outline font standard developed by Apple in the late 1980s as a competitor to Adobe\'s Type 1 fonts used in PostScript. It has become the most common format for fonts on the classic Mac OS, macOS, and Microsoft Windows operating systems.',
		'Binary',
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=869&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/TrueType'
		)
	),

	array(
		0,
		48,
		'sr',
		'\\x4f\\x54\\x54\\x4f[\\x00-\\xff]{8,40}\\x43\\x46\\x46\\x20',
		0,
		NULL,
		's',
		'',
		'otf',
		array(
			'otf',
			'otc',
			'ttf',
			'ttc'
		),
		'font/otf',
		'public.opentype-font',
		'OpenType Font File',
		NULL,
		NULL,
		'fmt/520',
		NULL,
		'Font',
		NULL,
		'OpenType is a format for scalable computer fonts. It was built on its predecessor TrueType, retaining TrueType\'s basic structure and adding many intricate data structures for prescribing typographic behavior. OpenType is a registered trademark of Microsoft Corporation.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1307&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/OpenType',
			'https://docs.microsoft.com/en-us/typography/opentype/spec/'
		)
	),

	array(
		0,
		NULL,
		'h',
		'fd377a585a00',
		0,
		NULL,
		's',
		'',
		'xz',
		array(
			'xz'
		),
		'application/x-xz',
		NULL,
		'XZ File Format',
		NULL,
		NULL,
		'fmt/1098',
		NULL,
		'Aggregate',
		NULL,
		'The .xz file format is a container format for compressed streams. There are no archiving capabilities, that is, the .xz format can hold only a single file just like the .gz and .bz2 file formats used by gzip and bzip2, respectively.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1907&strPageToDisplay=summary',
			'https://tukaani.org/xz/format.html',
			'https://tukaani.org/xz/xz-file-format.txt'
		)
	),

	array(
		0,
		NULL,
		'h',
		'78617221001C',
		0,
		NULL,
		's',
		'',
		'xar',
		array(
			'xar',
			'pkg',
			'xip'
		),
		'application/x-xar',
		'com.apple.xar-archive',
		'eXtensible ARchive format',
		NULL,
		NULL,
		'fmt/600',
		NULL,
		'Aggregate',
		NULL,
		'XAR (short for eXtensible ARchive format) is an open source file archiver and the archiver’s file format. It was created within the OpenDarwin project and is used in macOS X 10.5 and up for software installation routines, as well as browser extensions in Safari 5.0 and up. Xar replaced the use of gzipped pax files.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1392&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/Xar_(archiver)',
		)
	),

	array(
		0,
		23,
		'sr',
		'\\x00\\x00\\x00\\x0c\\x6a\\x50\\x20\\x20\\x0d\\x0a\\x87\\x0a[\\x00-\\xff]{4}\\x66\\x74\\x79\\x70\\x6a\\x70\\x32',
		0,
		NULL,
		's',
		'',
		'jp2',
		array(
			'jp2',
			'j2k'
		),
		'image/jp2',
		'public.jpeg-2000',
		'JP2 (JPEG 2000 part 1)',
		NULL,
		NULL,
		'x-fmt/392',
		'JPEG 2000',
		'Image (Raster)',
		NULL,
		'JPEG 2000 (JP2) is an image compression standard and coding system. It was developed from 1997 to 2000 by a Joint Photographic Experts Group committee chaired by Touradj Ebrahimi (later the JPEG president), with the intention of superseding their original discrete cosine transform (DCT) based JPEG standard (created in 1992) with a newly designed, wavelet-based method. The standardized filename extension is .jp2 for ISO/IEC 15444-1 conforming files and .jpx for the extended part-2 specifications, published as ISO/IEC 15444-2. The registered MIME types are defined in RFC 3745. For ISO/IEC 15444-1 it is image/jp2.',
		'Text',
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=686&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/JPEG_2000',
			'http://fileformats.archiveteam.org/wiki/JPEG_2000',
			'https://jpeg.org/jpeg2000/'
		)
	),

	array(
		0,
		23,
		'sr',
		'\\x00\\x00\\x00\\x0c\\x6a\\x50\\x20\\x20\\x0d\\x0a\\x87\\x0a[\\x00-\\xff]{4}\\x66\\x74\\x79\\x70\\x6a\\x70\\x78',
		0,
		NULL,
		's',
		'',
		'jpx',
		array(
			'jpx',
			'jpf'
		),
		'image/jpx',
		'public.jpeg-2000',
		'JPX (JPEG 2000 part 2)',
		NULL,
		'JPF',
		'fmt/151',
		'JPEG 2000',
		'Image (Raster)',
		NULL,
		'JPX is an image file format extended from JP2, providing the potential for extended colour space support, alternative compression methods, and other features. JPX may be used in applications that require additional functionality or data structures beyond those defined in the JP2 file format.',
		'Text',
		'Big-endian (Motorola)',
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=686&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/JPEG_2000',
			'http://fileformats.archiveteam.org/wiki/JPEG_2000',
			'https://jpeg.org/jpeg2000/'
		)
	),

	array(
		0,
		24,
		'sr',
		'\\x00\\x00\\x00\\x0c\\x6a\\x50\\x20\\x20\\x0d\\x0a\\x87\\x0a[\\x00-\\xff]{4}\\x66\\x74\\x79\\x70\\x6d\\x6a\\x70\\x32',
		0,
		NULL,
		's',
		'',
		'mj2',
		array(
			'mj2',
			'mjp2'
		),
		'video/mj2',
		NULL,
		'MJ2 (Motion JPEG 2000)',
		NULL,
		NULL,
		'fmt/337',
		'JPEG 2000',
		'Video',
		NULL,
		'Motion JPEG 2000 (MJ2 or MJP2) is a file format for motion sequences of JPEG 2000 images and associated audio, based on the MP4/QuickTime format. Filename extensions for Motion JPEG 2000 video files are .mj2 and .mjp2, as defined in RFC 3745.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1082&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/Motion_JPEG_2000',
			'http://fileformats.archiveteam.org/wiki/JPEG_2000',
			'https://jpeg.org/jpeg2000/'
		)
	),

	array(
		0,
		23,
		'sr',
		'\\x00\\x00\\x00\\x0c\\x6a\\x50\\x20\\x20\\x0d\\x0a\\x87\\x0a[\\x00-\\xff]{4}\\x66\\x74\\x79\\x70\\x6a\\x70\\x6d',
		0,
		NULL,
		's',
		'',
		'jpm',
		array(
			'jpm'
		),
		'image/jpm',
		NULL,
		'JPM (JPEG 2000 part 6)',
		NULL,
		NULL,
		'fmt/463',
		'JPEG 2000',
		'Image (Raster)',
		NULL,
		'The JPEG 2000 Compound Image File Format is an optional file format for storing compound images using the JPEG 2000 file format family architecture. A compound image is an image that may contain scanned images, synthetic images or both, and that preferably requires a mix of continuous tone and bi-level compression methods.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1250&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/JPEG_2000',
			'http://fileformats.archiveteam.org/wiki/JPEG_2000',
			'https://jpeg.org/jpeg2000/'
		)
	),

	array(
		0,
		NULL,
		'h',
		'ff4fff51',
		0,
		NULL,
		's',
		'',
		'jpc',
		array(
			'jpc'
		),
		'image/jpc',
		NULL,
		'JPC (JPEG 2000 codestream)',
		NULL,
		NULL,
		NULL,
		'JPEG 2000',
		'Image (Raster)',
		NULL,
		'JPEG 2000 codestream (also known as J2K, J2C, or JPEG 2000 Part 1, Core Coding System) is the wavelet-based compressed image format defined in Part 1 of the JPEG 2000 standard. Both lossy and lossless compression are supported.',
		NULL,
		NULL,
		array(
			'http://fileformats.archiveteam.org/wiki/JPEG_2000_codestream',
			'https://en.wikipedia.org/wiki/JPEG_2000',
			'http://fileformats.archiveteam.org/wiki/JPEG_2000',
			'https://jpeg.org/jpeg2000/'
		)
	),

	array(
		0,
		18,
		'sr',
		'\\x4d\\x54\\x68\\x64\\x00\\x00\\x00\\x06\\x00[\\x00-\\x02][\\x00-\\xff]{4}\\x4d\\x54\\x72\\x6b',
		0,
		NULL,
		's',
		'',
		'mid',
		array(
			'kar',
			'mid',
			'midi'
		),
		'audio/midi',
		NULL,
		'MIDI Audio',
		NULL,
		NULL,
		'fmt/230',
		NULL,
		'Audio',
		NULL,
		'MIDI (an acronym for Musical Instrument Digital Interface) is a technical standard that describes a communications protocol, digital interface, and electrical connectors that connect a wide variety of electronic musical instruments, computers, and related audio devices for playing, editing and recording music. The specification originates in a paper published by Dave Smith and Chet Wood then of Sequential Circuits at the October 1981 Audio Engineering Society conference in New York City then titled Universal Synthesizer Interface.',
		'Text',
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=322&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/MIDI',
			'https://www.midi.org/specifications/item/the-midi-1-0-specification/'
		)
	),

	array(
//		0,
//		156,
//		'sr',
//		'[\\x21-\\xef][\\x00-\\xff]{104}[\\x30-\\x37][\\x20-\\x37]\\x00[\\x00-\\xff]{5}[\\x30-\\x37][\\x20-\\x37]\\x00[\\x00-\\xff]{5}[\\x30-\\x37][\\x20-\\x37]\\x00[\\x00-\\xff]{10}[\\x30-\\x37][\\x00\\x20][\\x00-\\xff]{10}[\\x30-\\x37][\\x00\\x20][\\x00-\\xff]{5}[\\x30-\\x37][\\x00-\\x37][\\x00\\x20]',
		257,
		8,
		'sr',
		'\\x75\\x73\\x74\\x61\\x72(?:\\x00\\x30\\x30|\\x20\\x20\\x00)',
		0,
		NULL,
		's',
		'',
		'tar',
		array(
			'tar'
		),
		'application/x-tar',
		'public.tar-archive',
		'Tape Archive Format',
		NULL,
		'tar',
		'x-fmt/265',
		NULL,
		'Aggregate',
		NULL,
		'In computing, tar is a computer software utility for collecting many files into one archive file, often referred to as a tarball, for distribution or backup purposes. The name is derived from "tape archive", as it was originally developed to write data to sequential I/O devices with no file system of their own. The archive data sets created by tar contain various file system parameters, such as name, timestamps, ownership, file-access permissions, and directory organization.',
		'Text',
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=385&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/Tar_(computing)'
		)
	),

	array(
		0,
		NULL,
		'h',
//		'7b5c727466',
		'7b5c72746631',
		0,
		NULL,
		's',
		'',
		'rtf',
		array(
			'rtf'
		),
		array(
			'application/rtf',
			'text/rtf'
		),
		'public.rtf',
		'Rich Text Format',
		NULL,
		NULL,
		NULL,
		'Rich Text Format',
		'Word Processor',
		'Full',
		'Rich Text Format (RTF) is a format developed by Microsoft to encode formatted text and graphics for use within applications or for data and formatting transfer between applications. It is closely associated with Microsoft Word, and new versions are developed with each release of Word. RTF files are encoded in plain text, typically ASCII, and consists of a series of control words, control symbols and groups. An RTF file comprises a Header section, containing various header tables, followed by a Document section, containing the document content and formatting.',
		'Text',
		NULL,
		array(
			'https://en.wikipedia.org/wiki/Rich_Text_Format',
			'http://latex2rtf.sourceforge.net/RTF-Spec-1.0.txt',
			'http://latex2rtf.sourceforge.net/RTF-Spec-1.2.pdf',
			'http://www.snake.net/software/RTF/RTF-Spec-1.3.rtf',
			'http://latex2rtf.sourceforge.net/RTF-Spec-1.3.txt',
			'http://www.biblioscape.com/rtf15_spec.htm',
			'https://interoperability.blob.core.windows.net/files/Archive_References/%5bMSFT-RTF%5d.pdf',
			'https://web.archive.org/web/20190708132914if_/http://www.kleinlercher.at/tools/Windows_Protocols/Word2007RTFSpec9.pdf'
		)
	),

	array(
		0,
		12,
		'sr',
		'\\x52\\x49\\x46\\x46[\\x00-\\xff]{4}\\x57\\x41\\x56\\x45',
		0,
		NULL,
		's',
		'',
		'wav',
		array(
			'wav'
		),
		'audio/x-wav',
		NULL,
		'Waveform Audio File Format (WAVE/WAV)',
		NULL,
		NULL,
		NULL,
		'WAVE',
		'Audio',
		'Full',
		'Waveform Audio File Format (WAVE, or WAV due to its filename extension) is an audio file format standard, developed by IBM and Microsoft, for storing an audio bitstream on PCs. It is an application of the Resource Interchange File Format (RIFF) bitstream format method for storing data in "chunks", and thus is also close to the 8SVX and the AIFF format used on Amiga and Macintosh computers, respectively. It is the main format used on Microsoft Windows systems for raw and typically uncompressed audio. The usual bitstream encoding is the linear pulse-code modulation (LPCM) format.',
		'Binary',
		'Little-endian (Intel)',
		array(
			'https://en.wikipedia.org/wiki/WAV',
			'http://www-mmsp.ece.mcgill.ca/Documents/AudioFormats/WAVE/WAVE.html'
		)
	),

	array(
		0,
		NULL,
		'h',
		'edabeedb01',
		0,
		NULL,
		's',
		'',
		'rpm',
		array(
			'rpm'
		),
		NULL,
		NULL,
		'RPM Package Manager file',
		'1',
		NULL,
		'fmt/793',
		NULL,
		NULL,
		NULL,
		'RPM Package Manager file (RPM) was previously known as a Red Hat Package Manager File. It is a file used by RPM Package Manager which is a package management system. RPM files currently appear in two defined types, the first is binary package files containing the compiled version of certain software. The second is source package files containing the source code used to produce a package. These have an appropriate tag in the file header that distinguishes them from Binary RPMs, causing them to be extracted to /usr/src on installation. Source package files customarily carry the file extension “.src.rpm".',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1592&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/RPM_Package_Manager'
		)
	),

	array(
		0,
		NULL,
		'h',
		'edabeedb02',
		0,
		NULL,
		's',
		'',
		'rpm',
		array(
			'rpm'
		),
		NULL,
		NULL,
		'RPM Package Manager file',
		'2',
		NULL,
		'fmt/794',
		NULL,
		NULL,
		NULL,
		'RPM Package Manager file (RPM) was previously known as a Red Hat Package Manager File. It is a file used by RPM Package Manager which is a package management system. RPM files currently appear in two defined types, the first is binary package files containing the compiled version of certain software. The second is source package files containing the source code used to produce a package. These have an appropriate tag in the file header that distinguishes them from Binary RPMs, causing them to be extracted to /usr/src on installation. Source package files customarily carry the file extension “.src.rpm".',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1593&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/RPM_Package_Manager'
		)
	),

	array(
		0,
		NULL,
		'h',
		'edabeedb0300',
		0,
		NULL,
		's',
		'',
		'rpm',
		array(
			'rpm'
		),
		NULL,
		NULL,
		'RPM Package Manager file',
		'3',
		NULL,
		'fmt/795',
		NULL,
		NULL,
		NULL,
		'RPM Package Manager file (RPM) was previously known as a Red Hat Package Manager File. It is a file used by RPM Package Manager which is a package management system. RPM files currently appear in two defined types, the first is binary package files containing the compiled version of certain software. The second is source package files containing the source code used to produce a package. These have an appropriate tag in the file header that distinguishes them from Binary RPMs, causing them to be extracted to /usr/src on installation. Source package files customarily carry the file extension “.src.rpm".',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1594&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/RPM_Package_Manager'
		)
	),

	array(
		0,
		NULL,
		'h',
		'526172211a0700',
		0,
		NULL,
		's',
		'',
		'rar',
		array(
			'rar'
		),
		array(
			'application/x-rar-compressed',
			'application/vnd.rar'
		),
		'com.rarlab.rar-archive',
		'RAR Archive',
		'1.5-4.0',
		NULL,
		NULL,
		NULL,
		NULL,
		NULL,
		'RAR is a proprietary archive file format that supports data compression, error recovery and file spanning.',
		NULL,
		NULL,
		array(
			'https://en.wikipedia.org/wiki/RAR_(file_format)'
		)
	),

	array(
		0,
		45,
		'sr',
		'\\x52\\x61\\x72\\x21\\x1a\\x07\\x00[\\x00-\\xff]{2}\\x73[\\x00-\\xff]{34}\\x14',
		0,
		NULL,
		's',
		'',
		'rar',
		array(
			'rar'
		),
		array(
			'application/x-rar-compressed',
			'application/vnd.rar'
		),
		'com.rarlab.rar-archive',
		'RAR Archive',
		'2.0',
		NULL,
		'x-fmt/264',
		NULL,
		NULL,
		NULL,
		'RAR is a proprietary archive file format that supports data compression, error recovery and file spanning.',
		'Text',
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=384&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/RAR_(file_format)'
		)
	),

	array(
		0,
		45,
		'sr',
		'\\x52\\x61\\x72\\x21\\x1a\\x07\\x00[\\x00-\\xff]{2}\\x73[\\x00-\\xff]{34}\\x1d',
		0,
		NULL,
		's',
		'',
		'rar',
		array(
			'rar'
		),
		array(
			'application/x-rar-compressed',
			'application/vnd.rar'
		),
		'com.rarlab.rar-archive',
		'RAR Archive',
		'2.9',
		NULL,
		'fmt/411',
		NULL,
		NULL,
		NULL,
		'RAR is a proprietary archive file format that supports data compression, error recovery and file spanning.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1159&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/RAR_(file_format)'
		)
	),

	array(
		0,
		NULL,
		'h',
		'526172211a070100',
		0,
		NULL,
		's',
		'',
		'rar',
		array(
			'rar'
		),
		array(
			'application/x-rar-compressed',
			'application/vnd.rar'
		),
		'com.rarlab.rar-archive',
		'RAR Archive',
		'5.0',
		NULL,
		'fmt/613',
		NULL,
		'Aggregate',
		NULL,
		'RAR is a proprietary archive file format that supports data compression, error recovery and file spanning.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1409&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/RAR_(file_format)'
		)
	),

	array(
		0,
		NULL,
		'h',
		'38425053000100000000000000',
		0,
		NULL,
		's',
		'',
		'psd',
		array(
			'pdd',
			'psd'
		),
		'image/vnd.adobe.photoshop',
		'com.adobe.photoshop-image',
		'Adobe Photoshop',
		NULL,
		NULL,
		'x-fmt/92',
		NULL,
		'Image (Raster)',
		NULL,
		'Adobe Photoshop is a raster graphics editor developed and published by Adobe Systems for Windows and OS X. Files natively produced by Adobe PhotoDeluxe (extension pdd) also appear to be based upon the Adobe Photoshop file format.',
		'Text',
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=139&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/Adobe_Photoshop'
		)
	),

	array(
		0,
		NULL,
		'h',
		'384250530002000000000000',
		0,
		NULL,
		's',
		'',
		'psb',
		array(
			'psb'
		),
		'image/vnd.adobe.photoshop',
		'com.adobe.photoshop-image',
		'Adobe Photoshop',
		NULL,
		NULL,
		'fmt/996',
		NULL,
		'Image (Raster)',
		NULL,
		'Adobe Photoshop is a raster graphics editor developed and published by Adobe Systems. The Large Document Format supports documents of up to 300,000 pixels in any dimension as opposed to the PSD limit of 30,000 pixels.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1801&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/Adobe_Photoshop'
		)
	),

	array(
		0,
		5,
		'sr',
		'(\\x46\\x4f\\x52\\x4d|\\x4c\\x49\\x53\\x54|\\x43\\x41\\x54\\x20)\\x00',
		0,
		NULL,
		's',
		'',
		'iff',
		array(
			'iff'
		),
		NULL,
		NULL,
		'Interchange File',
		NULL,
		NULL,
		'x-fmt/157',
		NULL,
		'Aggregate',
		NULL,
		'This is an outline record only, and requires further details, research or authentication to provide information that will enable users to further understand the format and to assess digital preservation risks associated with it if appropriate. If you are able to help by supplying any additional information concerning this entry, please return to the main PRONOM page and select ‘Add an Entry’.',
		'Text',
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=221&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/Interchange_File_Format'
		)
	),

	array(
		0,
		16,
		'sr',
		'\\x46\\x4f\\x52\\x4d[\\x00-\\xff]{4}\\x49\\x4c\\x42\\x4d\\x42\\x4d\\x48\\x44',
		0,
		NULL,
		's',
		'',
		'iff',
		array(
			'lbm',
			'iff'
		),
		NULL,
		NULL,
		'Interchange File Format Interleaved Bitmap',
		NULL,
		NULL,
		'fmt/338',
		NULL,
		'Image (Raster)',
		NULL,
		'This is an outline record only, and requires further details, research or authentication to provide information that will enable users to further understand the format and to assess digital preservation risks associated with it if appropriate. If you are able to help by supplying any additional information concerning this entry, please return to the main PRONOM page and select ‘Add an Entry’.',
		NULL,
		'Big-endian (Motorola)',
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1083&strPageToDisplay=summary'
		)
	),

	array(
		0,
		12,
		'sr',
		'\\x46\\x4f\\x52\\x34[\\x00-\\xff]{4}\\x43\\x49\\x4d\\x47',
		0,
		NULL,
		's',
		'',
		'iff',
		array(
			'ico',
			'iff'
		),
		NULL,
		NULL,
		'Maya IFF Image File',
		NULL,
		NULL,
		'fmt/1169',
		NULL,
		'Image (Raster)',
		NULL,
		'Maya is a 3D graphics application published by Autodesk. Maya IFF files are raster images used for images and textures within Maya.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1979&strPageToDisplay=summary'
		)
	),

	array(
		0,
		16,
		'sr',
		'\\x46\\x4f\\x52\\x4d[\\x00-\\xff]{4}\\x38\\x53\\x56\\x58\\x56\\x48\\x44\\x52',
		0,
		NULL,
		's',
		'',
		'iff',
		array(
			'8svx',
			'iff'
		),
		NULL,
		NULL,
		'Interchange File Format 8-bit Sampled Voice',
		NULL,
		NULL,
		'fmt/339',
		NULL,
		'Audio',
		NULL,
		'This is an outline record only, and requires further details, research or authentication to provide information that will enable users to further understand the format and to assess digital preservation risks associated with it if appropriate. If you are able to help by supplying any additional information concerning this entry, please return to the main PRONOM page and select ‘Add an Entry’.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1084&strPageToDisplay=summary'
		)
	),

	array(
		32769,
		16395,
		'sr',
		'\\x43\\x44\\x30\\x30\\x31[\\x00-\\xff]{1,16384}\\xff\\x43\\x44\\x30\\x30\\x31',
		0,
		NULL,
		's',
		'',
		'iso',
		array(
			'iso',
			'udf'
		),
		'application/x-iso9660-image',
		NULL,
		'ISO Disk Image File',
		NULL,
		NULL,
		'fmt/468',
		NULL,
		NULL,
		NULL,
		'An ISO image is a disk image of an optical disc. In other words, it is an archive file that contains everything that would be written to an optical disc, sector by sector, including the optical disc file system. ISO image files bear the .iso filename extension. The name ISO is taken from the International Organization for Standardization (ISO) ISO 9660 file system used with CD-ROM media, but what is known as an ISO image might also contain a UDF (ISO/IEC 13346) file system (commonly used by DVDs and Blu-ray Discs).',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1255&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/ISO_image'
		)
	),

	array(
		0,
		4,
		'sr',
		'\\x50\\x32(\\x20|\\x09|\\x0d\\x0a|\\x0a)',
		0,
		NULL,
		's',
		'',
		'pgm',
		array(
			'pgm',
			'pgma'
		),
		'image/x-portable-graymap',
		NULL,
		'Portable Grey Map - ASCII',
		NULL,
		NULL,
		'fmt/407',
		NULL,
		NULL,
		NULL,
		'Several graphics formats are used and defined by the Netpbm project. The portable pixmap format (PPM), the portable graymap format (PGM) and the portable bitmap format (PBM) are image file formats designed to be easily exchanged between platforms. They are also sometimes referred to collectively as the portable anymap format (PNM), not to be confused with the related portable arbitrary map format.',
		'Text',
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1155&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/Netpbm'
		)
	),

	array(
		0,
		83,
		'sr',
		'\\x50\\x35[\\x20\\x0d\\x0a][\\x00-\\xff]{0,2}\\x23[\\x00-\\xff]{0,70}\\x0a[\\x30-\\x39\\x20\\x0a\\x0d]{6}',
		0,
		NULL,
		's',
		'',
		'pgm',
		array(
			'pgm',
			'pgmb'
		),
		'image/x-portable-graymap',
		NULL,
		'Portable Grey Map - Binary with comment',
		NULL,
		NULL,
		'fmt/406',
		NULL,
		NULL,
		NULL,
		'Several graphics formats are used and defined by the Netpbm project. The portable pixmap format (PPM), the portable graymap format (PGM) and the portable bitmap format (PBM) are image file formats designed to be easily exchanged between platforms. They are also sometimes referred to collectively as the portable anymap format (PNM), not to be confused with the related portable arbitrary map format.',
		'Binary',
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1154&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/Netpbm'
		)
	),

	array(
		0,
		9,
		'sr',
		'\\x50\\x35[\\x20\\x0d\\x0a][\\x30-\\x39\\x20\\x0a\\x0d]{6}',
		0,
		NULL,
		's',
		'',
		'pgm',
		array(
			'pgm',
			'pgmb'
		),
		'image/x-portable-graymap',
		NULL,
		'Portable Grey Map - Binary without comment',
		NULL,
		NULL,
		'fmt/406',
		NULL,
		NULL,
		NULL,
		'Several graphics formats are used and defined by the Netpbm project. The portable pixmap format (PPM), the portable graymap format (PGM) and the portable bitmap format (PBM) are image file formats designed to be easily exchanged between platforms. They are also sometimes referred to collectively as the portable anymap format (PNM), not to be confused with the related portable arbitrary map format.',
		'Binary',
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1154&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/Netpbm'
		)
	),

	array(
		0,
		4,
		'sr',
		'\\x50\\x31(\\x20|\\x09|\\x0d\\x0a|\\x0a)',
		0,
		NULL,
		's',
		'',
		'pbm',
		array(
			'pbm',
			'pbma'
		),
		'image/x-portable-bitmap',
		NULL,
		'Portable Bitmap Image - ASCII',
		NULL,
		NULL,
		'x-fmt/164',
		NULL,
		'Image (Raster)',
		NULL,
		'Several graphics formats are used and defined by the Netpbm project. The portable pixmap format (PPM), the portable graymap format (PGM) and the portable bitmap format (PBM) are image file formats designed to be easily exchanged between platforms. They are also sometimes referred to collectively as the portable anymap format (PNM), not to be confused with the related portable arbitrary map format.',
		'Text',
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=236&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/Netpbm'
		)
	),

	array(
		0,
		81,
		'sr',
		'\\x50\\x34[\\x20\\x0d\\x0a][\\x00-\\xff]{0,2}\\x23[\\x00-\\xff]{0,70}\\x0a[\\x30-\\x39\\x20\\x0a\\x0d]{4}',
		0,
		NULL,
		's',
		'',
		'pbm',
		array(
			'pbm',
			'pbmb'
		),
		'image/x-portable-bitmap',
		NULL,
		'Portable Bitmap Image - Binary with comment',
		NULL,
		NULL,
		'fmt/409',
		NULL,
		NULL,
		NULL,
		'Several graphics formats are used and defined by the Netpbm project. The portable pixmap format (PPM), the portable graymap format (PGM) and the portable bitmap format (PBM) are image file formats designed to be easily exchanged between platforms. They are also sometimes referred to collectively as the portable anymap format (PNM), not to be confused with the related portable arbitrary map format.',
		'Binary',
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1157&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/Netpbm'
		)
	),

	array(
		0,
		7,
		'sr',
		'\\x50\\x34[\\x20\\x0d\\x0a][\\x30-\\x39\\x20\\x0a\\x0d]{4}',
		0,
		NULL,
		's',
		'',
		'pbm',
		array(
			'pbm',
			'pbmb'
		),
		'image/x-portable-bitmap',
		NULL,
		'Portable Bitmap Image - Binary without comment',
		NULL,
		NULL,
		'fmt/409',
		NULL,
		NULL,
		NULL,
		'Several graphics formats are used and defined by the Netpbm project. The portable pixmap format (PPM), the portable graymap format (PGM) and the portable bitmap format (PBM) are image file formats designed to be easily exchanged between platforms. They are also sometimes referred to collectively as the portable anymap format (PNM), not to be confused with the related portable arbitrary map format.',
		'Binary',
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1157&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/Netpbm'
		)
	),

	array(
		0,
		4,
		'sr',
		'\\x50\\x33(\\x20|\\x09|\\x0d\\x0a|\\x0a)',
		0,
		NULL,
		's',
		'',
		'ppm',
		array(
			'ppm',
			'ppma'
		),
		'image/x-portable-pixmap',
		NULL,
		'Portable Pixel Map - ASCII',
		NULL,
		NULL,
		'x-fmt/178',
		NULL,
		'Image (Raster)',
		NULL,
		'Several graphics formats are used and defined by the Netpbm project. The portable pixmap format (PPM), the portable graymap format (PGM) and the portable bitmap format (PBM) are image file formats designed to be easily exchanged between platforms. They are also sometimes referred to collectively as the portable anymap format (PNM), not to be confused with the related portable arbitrary map format.',
		'Text',
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=251&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/Netpbm'
		)
	),

	array(
		0,
		83,
		'sr',
		'\\x50\\x36[\\x20\\x0d\\x0a][\\x00-\\xff]{0,2}\\x23[\\x00-\\xff]{0,70}\\x0a[\\x30-\\x39\\x20\\x0a\\x0d]{6}',
		0,
		NULL,
		's',
		'',
		'ppm',
		array(
			'ppm',
			'ppmb'
		),
		'image/x-portable-pixmap',
		NULL,
		'Portable Pixel Map - Binary with comment',
		NULL,
		NULL,
		'fmt/408',
		NULL,
		NULL,
		NULL,
		'Several graphics formats are used and defined by the Netpbm project. The portable pixmap format (PPM), the portable graymap format (PGM) and the portable bitmap format (PBM) are image file formats designed to be easily exchanged between platforms. They are also sometimes referred to collectively as the portable anymap format (PNM), not to be confused with the related portable arbitrary map format.',
		'Binary',
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1156&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/Netpbm'
		)
	),

	array(
		0,
		9,
		'sr',
		'\\x50\\x36[\\x20\\x0d\\x0a][\\x30-\\x39\\x20\\x0a\\x0d]{6}',
		0,
		NULL,
		's',
		'',
		'ppm',
		array(
			'ppm',
			'ppmb'
		),
		'image/x-portable-pixmap',
		NULL,
		'Portable Pixel Map - Binary without comment',
		NULL,
		NULL,
		'fmt/408',
		NULL,
		NULL,
		NULL,
		'Several graphics formats are used and defined by the Netpbm project. The portable pixmap format (PPM), the portable graymap format (PGM) and the portable bitmap format (PBM) are image file formats designed to be easily exchanged between platforms. They are also sometimes referred to collectively as the portable anymap format (PNM), not to be confused with the related portable arbitrary map format.',
		'Binary',
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1156&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/Netpbm'
		)
	),

	array(
		0,
		NULL,
		'h',
		'5037',
		0,
		NULL,
		's',
		'',
		'pam',
		array(
			'pam'
		),
		'image/x-portable-arbitrarymap',
		NULL,
		'Portable Any Map',
		NULL,
		NULL,
		'fmt/405',
		NULL,
		NULL,
		NULL,
		'Several graphics formats are used and defined by the Netpbm project. The portable pixmap format (PPM), the portable graymap format (PGM) and the portable bitmap format (PBM) are image file formats designed to be easily exchanged between platforms. They are also sometimes referred to collectively as the portable anymap format (PNM), not to be confused with the related portable arbitrary map format.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1153&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/Netpbm',
			'http://netpbm.sourceforge.net/doc/pam.html'
		)
	),

	array(
		0,
		NULL,
		'h',
		'49492a00',
		0,
		NULL,
		's',
		'',
		'tif',
		array(
			'tif',
			'tiff'
		),
		'image/tiff',
		'public.tiff',
		'Tagged Image File Format',
		NULL,
		'TIFF',
		'fmt/353',
		NULL,
		'Image (Raster)',
		'Full',
		'Tagged Image File Format, abbreviated TIFF or TIF, is a computer file format for storing raster graphics images, popular among graphic artists, the publishing industry, and photographers. TIFF is widely supported by scanning, faxing, word processing, optical character recognition, image manipulation, desktop publishing, and page-layout applications. The format was created by Aldus Corporation for use in desktop publishing. It published the latest version 6.0 in 1992, subsequently updated with an Adobe Systems copyright after the latter acquired Aldus in 1994. Several Aldus or Adobe technical notes have been published with minor extensions to the format, and several specifications have been based on TIFF 6.0, including TIFF/EP (ISO 12234-2), TIFF/IT (ISO 12639), TIFF-F (RFC 2306) and TIFF-FX (RFC 3949).',
		NULL,
		'Little-endian (Intel)',
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1099&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/TIFF',
			'https://tools.ietf.org/html/rfc3302',
			'https://tools.ietf.org/html/rfc3949',
			'https://tools.ietf.org/html/rfc3950',
			'https://tools.ietf.org/html/rfc2306',
			'https://tools.ietf.org/html/rfc1314',
			'https://cool.culturalheritage.org/bytopic/imaging/std/tiff4.html',
			'https://cool.culturalheritage.org/bytopic/imaging/std/tiff5.html',
			'https://www.adobe.io/content/dam/udp/en/open/standards/tiff/TIFF6.pdf',
			'https://www.loc.gov/preservation/digital/formats/fdd/fdd000022.shtml',
			'http://www.libtiff.org/support.html'
		)
	),

	array(
		0,
		NULL,
		'h',
		'4d4d002a',
		0,
		NULL,
		's',
		'',
		'tif',
		array(
			'tif',
			'tiff'
		),
		'image/tiff',
		'public.tiff',
		'Tagged Image File Format',
		NULL,
		'TIFF',
		'fmt/353',
		NULL,
		'Image (Raster)',
		'Full',
		'Tagged Image File Format, abbreviated TIFF or TIF, is a computer file format for storing raster graphics images, popular among graphic artists, the publishing industry, and photographers. TIFF is widely supported by scanning, faxing, word processing, optical character recognition, image manipulation, desktop publishing, and page-layout applications. The format was created by Aldus Corporation for use in desktop publishing. It published the latest version 6.0 in 1992, subsequently updated with an Adobe Systems copyright after the latter acquired Aldus in 1994. Several Aldus or Adobe technical notes have been published with minor extensions to the format, and several specifications have been based on TIFF 6.0, including TIFF/EP (ISO 12234-2), TIFF/IT (ISO 12639), TIFF-F (RFC 2306) and TIFF-FX (RFC 3949).',
		NULL,
		'Big-endian (Motorola)',
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1099&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/TIFF',
			'https://tools.ietf.org/html/rfc3302',
			'https://tools.ietf.org/html/rfc3949',
			'https://tools.ietf.org/html/rfc3950',
			'https://tools.ietf.org/html/rfc2306',
			'https://tools.ietf.org/html/rfc1314',
			'https://cool.culturalheritage.org/bytopic/imaging/std/tiff4.html',
			'https://cool.culturalheritage.org/bytopic/imaging/std/tiff5.html',
			'https://www.adobe.io/content/dam/udp/en/open/standards/tiff/TIFF6.pdf',
			'https://www.loc.gov/preservation/digital/formats/fdd/fdd000022.shtml',
			'http://www.libtiff.org/support.html'
		)
	),

	array(
		4,
		8,
		's',
		'ftypmif1',
		0,
		NULL,
		's',
		'',
		'heif',
		array(
			'heif'
		),
		'image/heif',
		'public.heif',
		'High Efficiency Image File Format',
		NULL,
		NULL,
		'fmt/1101',
		NULL,
		'Image (Raster)',
		NULL,
		'The High Efficiency Image File Format (HEIF) is an image format standard defined in ISO/IEC 23008-12 - MPEG-H Part 12. HEIF can store images, image properties, thumbnails and other derivatives, plus image metadata. It can support individual images and image sequences At this time the PRONOM identification signature does not distinguish between individual images and image sequences although this may be desirable.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1909&strPageToDisplay=summary',
			'https://nokiatech.github.io/heif/technical.html',
			'https://en.wikipedia.org/wiki/High_Efficiency_Image_File_Format',
		)
	),

	array(
		4,
		8,
		's',
		'ftypmsf1',
		0,
		NULL,
		's',
		'',
		'heif',
		array(
			'heif',
			'heifs'
		),
		'image/heif-sequence',
		'public.heif',
		'High Efficiency Image File Format',
		NULL,
		NULL,
		'fmt/1101',
		NULL,
		'Image (Sequence)',
		NULL,
		'The High Efficiency Image File Format (HEIF) is an image format standard defined in ISO/IEC 23008-12 - MPEG-H Part 12. HEIF can store images, image properties, thumbnails and other derivatives, plus image metadata. It can support individual images and image sequences At this time the PRONOM identification signature does not distinguish between individual images and image sequences although this may be desirable.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1909&strPageToDisplay=summary',
			'https://nokiatech.github.io/heif/technical.html',
			'https://en.wikipedia.org/wiki/High_Efficiency_Image_File_Format',
		)
	),

	array(
		4,
		8,
		's',
		'ftypheic',
		0,
		NULL,
		's',
		'',
		'heic',
		array(
			'heic'
		),
		'image/heic',
		'public.heic',
		'High Efficiency Image File Format / HEVC (Main or Main Still Picture profile)',
		NULL,
		NULL,
		'fmt/1101',
		NULL,
		'Image (Raster)',
		NULL,
		'The High Efficiency Image File Format (HEIF) is an image format standard defined in ISO/IEC 23008-12 - MPEG-H Part 12. HEIF can store images, image properties, thumbnails and other derivatives, plus image metadata. It can support individual images and image sequences At this time the PRONOM identification signature does not distinguish between individual images and image sequences although this may be desirable.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1909&strPageToDisplay=summary',
			'https://nokiatech.github.io/heif/technical.html',
			'https://en.wikipedia.org/wiki/High_Efficiency_Image_File_Format',
		)
	),

	array(
		4,
		8,
		's',
		'ftypheix',
		0,
		NULL,
		's',
		'',
		'heic',
		array(
			'heic'
		),
		'image/heic',
		'public.heic',
		'High Efficiency Image File Format / HEVC (Main 10 or format range extensions profile)',
		NULL,
		NULL,
		'fmt/1101',
		NULL,
		'Image (Raster)',
		NULL,
		'The High Efficiency Image File Format (HEIF) is an image format standard defined in ISO/IEC 23008-12 - MPEG-H Part 12. HEIF can store images, image properties, thumbnails and other derivatives, plus image metadata. It can support individual images and image sequences At this time the PRONOM identification signature does not distinguish between individual images and image sequences although this may be desirable.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1909&strPageToDisplay=summary',
			'https://nokiatech.github.io/heif/technical.html',
			'https://en.wikipedia.org/wiki/High_Efficiency_Image_File_Format',
		)
	),

	array(
		4,
		8,
		's',
		'ftyphevc',
		0,
		NULL,
		's',
		'',
		'heic',
		array(
			'heic',
			'heics'
		),
		'image/heic-sequence',
		'public.heic',
		'High Efficiency Image File Format / HEVC (Main or Main Still Picture profile)',
		NULL,
		NULL,
		'fmt/1101',
		NULL,
		'Image (Sequence)',
		NULL,
		'The High Efficiency Image File Format (HEIF) is an image format standard defined in ISO/IEC 23008-12 - MPEG-H Part 12. HEIF can store images, image properties, thumbnails and other derivatives, plus image metadata. It can support individual images and image sequences At this time the PRONOM identification signature does not distinguish between individual images and image sequences although this may be desirable.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1909&strPageToDisplay=summary',
			'https://nokiatech.github.io/heif/technical.html',
			'https://en.wikipedia.org/wiki/High_Efficiency_Image_File_Format',
		)
	),

	array(
		4,
		8,
		's',
		'ftyphevx',
		0,
		NULL,
		's',
		'',
		'heic',
		array(
			'heic',
			'heics'
		),
		'image/heic-sequence',
		'public.heic',
		'High Efficiency Image File Format / HEVC (Main 10 or format range extensions profile)',
		NULL,
		NULL,
		'fmt/1101',
		NULL,
		'Image (Sequence)',
		NULL,
		'The High Efficiency Image File Format (HEIF) is an image format standard defined in ISO/IEC 23008-12 - MPEG-H Part 12. HEIF can store images, image properties, thumbnails and other derivatives, plus image metadata. It can support individual images and image sequences At this time the PRONOM identification signature does not distinguish between individual images and image sequences although this may be desirable.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1909&strPageToDisplay=summary',
			'https://nokiatech.github.io/heif/technical.html',
			'https://en.wikipedia.org/wiki/High_Efficiency_Image_File_Format',
		)
	),

//	array(
//		4,
//		8,
//		'sr',
//		'\\x66\\x74\\x79\\x70(?:\\x6d\\x69\\x66\\x31|\\x6d\\x73\\x66\\x31|\\x68\\x65\\x69\\x63|\\x68\\x65\\x69\\x78|\\x68\\x65\\x76\\x63|\\x68\\x65\\x76\\x78)',
//		'ftyp(?:mif1|msf1|heic|heix|hevc|hevx)',
//		0,
//		NULL,
//		's',
//		'',
//		'heif',
//		array(
//			'heif',
//			'heifs',
//			'heic',
//			'heics'
//		),
//		array(
//			'image/heif',
//			'image/heif-sequence',
//			'image/heic',
//			'image/heic-sequence'
//		),
//		array(
//			'public.heif',
//			'public.heic'
//		),
//		'High Efficiency Image File Format',
//		NULL,
//		NULL,
//		'fmt/1101',
//		NULL,
//		'Image (Raster)',
//		NULL,
//		'The High Efficiency Image File Format (HEIF) is an image format standard defined in ISO/IEC 23008-12 - MPEG-H Part 12. HEIF can store images, image properties, thumbnails and other derivatives, plus image metadata. It can support individual images and image sequences At this time the PRONOM identification signature does not distinguish between individual images and image sequences although this may be desirable.',
//		NULL,
//		NULL,
//		array(
//			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1909&strPageToDisplay=summary',
//			'https://nokiatech.github.io/heif/technical.html',
//			'https://en.wikipedia.org/wiki/High_Efficiency_Image_File_Format',
//		)
//	),

	array(
		0,
		3,
		'sr',
		'\\xff\\xd8\\xff',
		0,
		NULL,
		'h',
		'ffd9',
		'jpg',
		array(
			'jpe',
			'jpeg',
			'jpg'
		),
		'image/jpeg',
		'public.jpeg',
		'Raw JPEG Stream',
		NULL,
		'JPEG',
		'fmt/41',
		NULL,
		'Image (Raster)',
		NULL,
		'The JPEG File Interchange Format (JFIF) is a file format for storing JPEG-compressed raster images. It was developed by the Independent JPEG Group and C-Cube Microsystems, in the absence of any such format being defined in the JPEG standard, and rapidly became a de facto standard; this is what is commonly referred to as the JPEG file format. A JFIF file comprises a JPEG data stream together with a JFIF marker. It begins with a Start of Image (SOI) marker, immediately followed by a JFIF Application (APP0). This is followed by the JPEG image data, which is terminated by an End of Image (EOI) marker. JFIF supports up to 24-bit colour and uses lossy compression (based on the Discrete Cosine Transform algorithm). Other types of compression are available through JPEG extensions, including progressive image buildup, arithmetic encoding, variable quantization, selective refinement, image tiling, and lossless compression, but these may not be supported by all JFIF readers and writers.',
		'Binary',
		'Big-endian (Motorola)',
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=670&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/JPEG_File_Interchange_Format',
		)
	),

	array(
		0,
		14,
		'sr',
		'\\xff\\xd8\\xff\\xe0[\\x00-\\xff]{2}\\x4a\\x46\\x49\\x46\\x00\\x01\\x00[\\x00\\x01\\x02]',
		0,
		NULL,
		'h',
		'ffd9',
		'jpg',
		array(
			'jpe',
			'jpeg',
			'jpg'
		),
		'image/jpeg',
		'public.jpeg',
		'JPEG File Interchange Format',
		'1.00',
		'JFIF (1.00)',
		'fmt/42',
		NULL,
		'Image (Raster)',
		'Full',
		'The JPEG File Interchange Format (JFIF) is a file format for storing JPEG-compressed raster images. It was developed by the Independent JPEG Group and C-Cube Microsystems, in the absence of any such format being defined in the JPEG standard, and rapidly became a de facto standard; this is what is commonly referred to as the JPEG file format. A JFIF file comprises a JPEG data stream together with a JFIF marker. It begins with a Start of Image (SOI) marker, immediately followed by a JFIF Application (APP0). This is followed by the JPEG image data, which is terminated by an End of Image (EOI) marker. JFIF supports up to 24-bit colour and uses lossy compression (based on the Discrete Cosine Transform algorithm). Other types of compression are available through JPEG extensions, including progressive image buildup, arithmetic encoding, variable quantization, selective refinement, image tiling, and lossless compression, but these may not be supported by all JFIF readers and writers.',
		'Binary',
		'Big-endian (Motorola)',
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=667&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/JPEG_File_Interchange_Format'
		)
	),

	array(
		0,
		14,
		'sr',
		'\\xff\\xd8\\xff\\xe0[\\x00-\\xff]{2}\\x4a\\x46\\x49\\x46\\x00\\x01\\x01[\\x00\\x01\\x02]',
		0,
		NULL,
		'h',
		'ffd9',
		'jpg',
		array(
			'jpe',
			'jpeg',
			'jpg'
		),
		'image/jpeg',
		'public.jpeg',
		'JPEG File Interchange Format',
		'1.01',
		'JFIF (1.01)',
		'fmt/43',
		NULL,
		'Image (Raster)',
		'Full',
		'The JPEG File Interchange Format (JFIF) is a file format for storing JPEG-compressed raster images. It was developed by the Independent JPEG Group and C-Cube Microsystems, in the absence of any such format being defined in the JPEG standard, and rapidly became a de facto standard; this is what is commonly referred to as the JPEG file format. A JFIF file comprises a JPEG data stream together with a JFIF marker. It begins with a Start of Image (SOI) marker, immediately followed by a JFIF Application (APP0). This is followed by the JPEG image data, which is terminated by an End of Image (EOI) marker. JFIF supports up to 24-bit colour and uses lossy compression (based on the Discrete Cosine Transform algorithm). Other types of compression are available through JPEG extensions, including progressive image buildup, arithmetic encoding, variable quantization, selective refinement, image tiling, and lossless compression, but these may not be supported by all JFIF readers and writers.',
		'Binary',
		'Big-endian (Motorola)',
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=668&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/JPEG_File_Interchange_Format'
		)
	),

	array(
		0,
		14,
		'sr',
		'\\xff\\xd8\\xff\\xe0[\\x00-\\xff]{2}\\x4a\\x46\\x49\\x46\\x00\\x01\\x02[\\x00\\x01\\x02]',
		0,
		NULL,
		'h',
		'ffd9',
		'jpg',
		array(
			'jpe',
			'jpeg',
			'jpg'
		),
		'image/jpeg',
		'public.jpeg',
		'JPEG File Interchange Format',
		'1.02',
		'JFIF (1.02)',
		'fmt/44',
		NULL,
		'Image (Raster)',
		'Full',
		'The JPEG File Interchange Format (JFIF) is a file format for storing JPEG-compressed raster images. It was developed by the Independent JPEG Group and C-Cube Microsystems, in the absence of any such format being defined in the JPEG standard, and rapidly became a de facto standard; this is what is commonly referred to as the JPEG file format. A JFIF file comprises a JPEG data stream together with a JFIF marker. It begins with a Start of Image (SOI) marker, immediately followed by a JFIF Application (APP0). This is followed by the JPEG image data, which is terminated by an End of Image (EOI) marker. JFIF supports up to 24-bit colour and uses lossy compression (based on the Discrete Cosine Transform algorithm). Other types of compression are available through JPEG extensions, including progressive image buildup, arithmetic encoding, variable quantization, selective refinement, image tiling, and lossless compression, but these may not be supported by all JFIF readers and writers.',
		'Binary',
		'Big-endian (Motorola)',
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=669&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/JPEG_File_Interchange_Format'
		)
	),

	array(
		0,
		16,
		'sr',
		'\\xff\\xd8\\xff\\xe1[\\x00-\\xff]{2}\\x45\\x78\\x69\\x66\\x00\\x00\\x4d\\x4d\\x00\\x2a',
		0,
		NULL,
		'h',
		'ffd9',
		'jpg',
		array(
			'jpe',
			'jpeg',
			'jpg'
		),
		'image/jpeg',
		'public.jpeg',
		'Exchangeable Image File Format (Compressed)',
		'2.0',
		'Exif Compressed Image (2.0)',
		'x-fmt/398',
		NULL,
		'Image (Raster)',
		NULL,
		'Exchangeable image file format (Exif) is a standard that specifies the formats for images, sound, and ancillary tags used by digital cameras (including smartphones), scanners and other systems handling image and sound files recorded by digital cameras.',
		'Binary',
		'Big-endian (Motorola)',
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=751&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/JPEG_File_Interchange_Format'
		)
	),

	array(
		0,
		16,
		'sr',
		'\\xff\\xd8\\xff\\xe1[\\x00-\\xff]{2}\\x45\\x78\\x69\\x66\\x00\\x00\\x49\\x49\\x2a\\x00',
		0,
		NULL,
		'h',
		'ffd9',
		'jpg',
		array(
			'jpe',
			'jpeg',
			'jpg'
		),
		'image/jpeg',
		'public.jpeg',
		'Exchangeable Image File Format (Compressed)',
		'2.0',
		'Exif Compressed Image (2.0)',
		'x-fmt/398',
		NULL,
		'Image (Raster)',
		NULL,
		'Exchangeable image file format (Exif) is a standard that specifies the formats for images, sound, and ancillary tags used by digital cameras (including smartphones), scanners and other systems handling image and sound files recorded by digital cameras.',
		'Binary',
		'Little-endian (Intel)',
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=751&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/JPEG_File_Interchange_Format'
		)
	),

	array(
		0,
		16,
		'sr',
		'\\xff\\xd8\\xff\\xe1[\\x00-\\xff]{2}\\x45\\x78\\x69\\x66\\x00\\x00\\x4d\\x4d\\x00\\x2a',
		0,
		NULL,
		'h',
		'ffd9',
		'jpg',
		array(
			'jpe',
			'jpeg',
			'jpg'
		),
		'image/jpeg',
		'public.jpeg',
		'Exchangeable Image File Format (Compressed)',
		'2.1',
		'Exif Compressed Image (2.1)',
		'x-fmt/390',
		NULL,
		'Image (Raster)',
		NULL,
		'Exchangeable image file format (Exif) is a standard that specifies the formats for images, sound, and ancillary tags used by digital cameras (including smartphones), scanners and other systems handling image and sound files recorded by digital cameras.',
		'Binary',
		'Big-endian (Motorola)',
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=675&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/JPEG_File_Interchange_Format'
		)
	),

	array(
		0,
		16,
		'sr',
		'\\xff\\xd8\\xff\\xe1[\\x00-\\xff]{2}\\x45\\x78\\x69\\x66\\x00\\x00\\x49\\x49\\x2a\\x00',
		0,
		NULL,
		'h',
		'ffd9',
		'jpg',
		array(
			'jpe',
			'jpeg',
			'jpg'
		),
		'image/jpeg',
		'public.jpeg',
		'Exchangeable Image File Format (Compressed)',
		'2.1',
		'Exif Compressed Image (2.1)',
		'x-fmt/390',
		NULL,
		'Image (Raster)',
		NULL,
		'Exchangeable image file format (Exif) is a standard that specifies the formats for images, sound, and ancillary tags used by digital cameras (including smartphones), scanners and other systems handling image and sound files recorded by digital cameras.',
		'Binary',
		'Little-endian (Intel)',
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=675&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/JPEG_File_Interchange_Format'
		)
	),

	array(
		0,
		16,
		'sr',
		'\\xff\\xd8\\xff\\xe1[\\x00-\\xff]{2}\\x45\\x78\\x69\\x66\\x00\\x00\\x4d\\x4d\\x00\\x2a',
		0,
		NULL,
		'h',
		'ffd9',
		'jpg',
		array(
			'jpe',
			'jpeg',
			'jpg'
		),
		'image/jpeg',
		'public.jpeg',
		'Exchangeable Image File Format (Compressed)',
		'2.2',
		'Exif Compressed Image (2.2)',
		'x-fmt/391',
		NULL,
		'Image (Raster)',
		NULL,
		'Exchangeable image file format (Exif) is a standard that specifies the formats for images, sound, and ancillary tags used by digital cameras (including smartphones), scanners and other systems handling image and sound files recorded by digital cameras.',
		'Binary',
		'Big-endian (Motorola)',
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=676&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/JPEG_File_Interchange_Format'
		)
	),

	array(
		0,
		16,
		'sr',
		'\\xff\\xd8\\xff\\xe1[\\x00-\\xff]{2}\\x45\\x78\\x69\\x66\\x00\\x00\\x49\\x49\\x2a\\x00',
		0,
		NULL,
		'h',
		'ffd9',
		'jpg',
		array(
			'jpe',
			'jpeg',
			'jpg'
		),
		'image/jpeg',
		'public.jpeg',
		'Exchangeable Image File Format (Compressed)',
		'2.2',
		'Exif Compressed Image (2.2)',
		'x-fmt/391',
		NULL,
		'Image (Raster)',
		NULL,
		'Exchangeable image file format (Exif) is a standard that specifies the formats for images, sound, and ancillary tags used by digital cameras (including smartphones), scanners and other systems handling image and sound files recorded by digital cameras.',
		'Binary',
		'Little-endian (Intel)',
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=676&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/JPEG_File_Interchange_Format'
		)
	),

	array(
		0,
		16,
		'sr',
		'\\xff\\xd8\\xff\\xe1[\\x00-\\xff]{2}\\x45\\x78\\x69\\x66\\x00\\x00\\x4d\\x4d\\x00\\x2a',
		0,
		NULL,
		'h',
		'ffd9',
		'jpg',
		array(
			'jpe',
			'jpeg',
			'jpg'
		),
		'image/jpeg',
		'public.jpeg',
		'Exchangeable Image File Format (Compressed)',
		'2.2.1',
		'Exif Compressed Image (2.2.1)',
		'fmt/645',
		NULL,
		'Image (Raster)',
		NULL,
		'Exchangeable image file format (Exif) is a standard that specifies the formats for images, sound, and ancillary tags used by digital cameras (including smartphones), scanners and other systems handling image and sound files recorded by digital cameras.',
		'Binary',
		'Big-endian (Motorola)',
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1444&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/JPEG_File_Interchange_Format'
		)
	),

	array(
		0,
		16,
		'sr',
		'\\xff\\xd8\\xff\\xe1[\\x00-\\xff]{2}\\x45\\x78\\x69\\x66\\x00\\x00\\x49\\x49\\x2a\\x00',
		0,
		NULL,
		'h',
		'ffd9',
		'jpg',
		array(
			'jpe',
			'jpeg',
			'jpg'
		),
		'image/jpeg',
		'public.jpeg',
		'Exchangeable Image File Format (Compressed)',
		'2.2.1',
		'Exif Compressed Image (2.2.1)',
		'fmt/645',
		NULL,
		'Image (Raster)',
		NULL,
		'Exchangeable image file format (Exif) is a standard that specifies the formats for images, sound, and ancillary tags used by digital cameras (including smartphones), scanners and other systems handling image and sound files recorded by digital cameras.',
		'Binary',
		'Little-endian (Intel)',
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1444&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/JPEG_File_Interchange_Format'
		)
	),

	array(
		0,
		38,
		'sr',
		'\\xff\\xd8\\xff\\xe8\\x00\\x20\\x53\\x50\\x49\\x46\\x46\\x00\\x01\\x00[\\x00-\\x04][\\x00-\\xff]{11}[\\x00-\\x05][\\x00-\\xff]{9}\\xff\\xe8',
		0,
		NULL,
		's',
		'',
		'jpg',
		array(
			'jpe',
			'jpeg',
			'jpg',
			'spf',
			'spiff'
		),
		'image/jpeg',
		'public.jpeg',
		'Still Picture Interchange File Format',
		'1.0',
		'SPIFF (1.0)',
		'fmt/112',
		NULL,
		'Image (Raster)',
		NULL,
		'The JPEG File Interchange Format (JFIF) is a file format for storing JPEG-compressed raster images. It was developed by the Independent JPEG Group and C-Cube Microsystems, in the absence of any such format being defined in the JPEG standard, and rapidly became a de facto standard; this is what is commonly referred to as the JPEG file format. A JFIF file comprises a JPEG data stream together with a JFIF marker. It begins with a Start of Image (SOI) marker, immediately followed by a JFIF Application (APP0). This is followed by the JPEG image data, which is terminated by an End of Image (EOI) marker. JFIF supports up to 24-bit colour and uses lossy compression (based on the Discrete Cosine Transform algorithm). Other types of compression are available through JPEG extensions, including progressive image buildup, arithmetic encoding, variable quantization, selective refinement, image tiling, and lossless compression, but these may not be supported by all JFIF readers and writers.',
		'Binary',
		'Big-endian (Motorola)',
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=671&strPageToDisplay=summary',
			'https://www.loc.gov/preservation/digital/formats/fdd/fdd000019.shtml',
			'https://en.wikipedia.org/wiki/JPEG_File_Interchange_Format',
			'http://www.fileformat.info/format/spiff/egff.htm',
			'https://web.archive.org/web/20111218193625if_/http://www.jpeg.org:80/public/spiff.pdf'
		)
	),

	array(
		0,
		2,
		'sr',
		'\\xff[\\xf2\\xf3\\xfb]',
//		NULL,
//		'h',
//		'fffb',
		0,
		NULL,
		's',
		'',
		'mp3',
		array(
			'mp3'
		),
		'audio/mpeg',
		'public.mp3',
		'MPEG 1/2 Audio Layer 3 without ID3v2 Tag',
		NULL,
		'MP3',
		NULL,
		NULL,
		'Audio',
		NULL,
		'MP3 is a digital audio format, formally known as MPEG-1 Audio Layer III or MPEG-2 Audio Layer III. A third variant, known as MPEG 2.5, which supports lower bit rates implemented by the LAME MP3 encoder among others, but is not a recognised standard.',
		NULL,
		NULL,
		array(
			'https://en.wikipedia.org/wiki/MP3',
			'https://en.wikipedia.org/wiki/List_of_file_signatures'
		)
	),

	array(
		0,
		NULL,
		's',
		'ID3',
		0,
		NULL,
		's',
		'',
		'mp3',
		array(
			'mp3'
		),
		'audio/mpeg',
		'public.mp3',
		'MPEG 1/2 Audio Layer 3 with ID3v2 Tag',
		NULL,
		'MP3',
		NULL,
		NULL,
		'Audio',
		NULL,
		'MP3 is a digital audio format, formally known as MPEG-1 Audio Layer III or MPEG-2 Audio Layer III. A third variant, known as MPEG 2.5, which supports lower bit rates implemented by the LAME MP3 encoder among others, but is not a recognised standard.',
		NULL,
		NULL,
		array(
			'https://en.wikipedia.org/wiki/MP3'
		)
	),

	array(
		0,
		NULL,
		's',
		'%!',
		0,
		NULL,
		's',
		'',
		'ps',
		array(
			'ps'
		),
		'application/postscript',
		'com.adobe.postscript',
		'Postscript',
		NULL,
		NULL,
		NULL,
		NULL,
		'Page Description',
		NULL,
		'Postscript is a computer language for creating vector graphics.',
		'Text',
		NULL,
		array(
			'https://en.wikipedia.org/wiki/PostScript',
			'https://web.archive.org/web/20170218093716if_/https://www.adobe.com/products/postscript/pdfs/PLRM.pdf',
			'https://web.archive.org/web/20160305010005if_/http://partners.adobe.com/public/developer/en/ps/PS3010and3011.Supplement.pdf',
			'https://web.archive.org/web/20150321034514if_/http://partners.adobe.com/public/developer/en/font/T1_SPEC.PDF',
			'https://w3-o.cs.hm.edu/users/ruckert/public_html/compiler/ThinkingInPostScript.pdf'
		)
	),

	array(
		0,
		NULL,
		'h',
		'252150532d41646f62652d312e30',
		0,
		7,
		'sr',
		'\\x25\\x25\\x45\\x4f\\x46(\\x0d|\\x0a|\\x0d\\x0a|\\x0a\\x0a)',
		'ps',
		array(
			'ps'
		),
		'application/postscript',
		'com.adobe.postscript',
		'Postscript',
		'1.0',
		NULL,
		'x-fmt/91',
		NULL,
		'Page Description',
		NULL,
		'Postscript is a computer language for creating vector graphics.',
		'Text',
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=138&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/PostScript',
			'https://web.archive.org/web/20170218093716if_/https://www.adobe.com/products/postscript/pdfs/PLRM.pdf',
			'https://web.archive.org/web/20160305010005if_/http://partners.adobe.com/public/developer/en/ps/PS3010and3011.Supplement.pdf',
			'https://web.archive.org/web/20150321034514if_/http://partners.adobe.com/public/developer/en/font/T1_SPEC.PDF',
			'https://w3-o.cs.hm.edu/users/ruckert/public_html/compiler/ThinkingInPostScript.pdf'
		)
	),

	array(
		0,
		NULL,
		'h',
		'252150532d41646f62652d322e30',
		0,
		NULL,
		's',
		'',
		'ps',
		array(
			'ps'
		),
		'application/postscript',
		'com.adobe.postscript',
		'Postscript',
		'2.0',
		NULL,
		'x-fmt/406',
		NULL,
		'Page Description',
		NULL,
		'Postscript is a computer language for creating vector graphics.',
		'Text',
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=771&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/PostScript',
			'https://web.archive.org/web/20170218093716if_/https://www.adobe.com/products/postscript/pdfs/PLRM.pdf',
			'https://web.archive.org/web/20160305010005if_/http://partners.adobe.com/public/developer/en/ps/PS3010and3011.Supplement.pdf',
			'https://web.archive.org/web/20150321034514if_/http://partners.adobe.com/public/developer/en/font/T1_SPEC.PDF',
			'https://w3-o.cs.hm.edu/users/ruckert/public_html/compiler/ThinkingInPostScript.pdf'
		)
	),

	array(
		0,
		NULL,
		'h',
		'252150532d41646f62652d322e31',
		0,
		NULL,
		's',
		'',
		'ps',
		array(
			'ps'
		),
		'application/postscript',
		'com.adobe.postscript',
		'Postscript',
		'2.1',
		NULL,
		'x-fmt/407',
		NULL,
		'Page Description',
		NULL,
		'Postscript is a computer language for creating vector graphics.',
		'Text',
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=772&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/PostScript',
			'https://web.archive.org/web/20170218093716if_/https://www.adobe.com/products/postscript/pdfs/PLRM.pdf',
			'https://web.archive.org/web/20160305010005if_/http://partners.adobe.com/public/developer/en/ps/PS3010and3011.Supplement.pdf',
			'https://web.archive.org/web/20150321034514if_/http://partners.adobe.com/public/developer/en/font/T1_SPEC.PDF',
			'https://w3-o.cs.hm.edu/users/ruckert/public_html/compiler/ThinkingInPostScript.pdf'
		)
	),

	array(
		0,
		NULL,
		'h',
		'252150532d41646f62652d332e30',
		0,
		NULL,
		's',
		'',
		'ps',
		array(
			'ps'
		),
		'application/postscript',
		'com.adobe.postscript',
		'Postscript',
		'3.0',
		NULL,
		'x-fmt/408',
		NULL,
		'Page Description',
		NULL,
		'Postscript is a computer language for creating vector graphics.',
		'Text',
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=773&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/PostScript',
			'https://web.archive.org/web/20170218093716if_/https://www.adobe.com/products/postscript/pdfs/PLRM.pdf',
			'https://web.archive.org/web/20160305010005if_/http://partners.adobe.com/public/developer/en/ps/PS3010and3011.Supplement.pdf',
			'https://web.archive.org/web/20150321034514if_/http://partners.adobe.com/public/developer/en/font/T1_SPEC.PDF',
			'https://w3-o.cs.hm.edu/users/ruckert/public_html/compiler/ThinkingInPostScript.pdf'
		)
	),

	array(
		0,
		NULL,
		'h',
		'252150532d41646f62652d332e31',
		0,
		NULL,
		's',
		'',
		'ps',
		array(
			'ps'
		),
		'application/postscript',
		'com.adobe.postscript',
		'Postscript',
		'3.1',
		NULL,
		'fmt/501',
		NULL,
		'Page Description',
		NULL,
		'Postscript is a computer language for creating vector graphics.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1288&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/PostScript',
			'https://web.archive.org/web/20170218093716if_/https://www.adobe.com/products/postscript/pdfs/PLRM.pdf',
			'https://web.archive.org/web/20160305010005if_/http://partners.adobe.com/public/developer/en/ps/PS3010and3011.Supplement.pdf',
			'https://web.archive.org/web/20150321034514if_/http://partners.adobe.com/public/developer/en/font/T1_SPEC.PDF',
			'https://w3-o.cs.hm.edu/users/ruckert/public_html/compiler/ThinkingInPostScript.pdf'
		)
	),

	array(
		0,
		25,
		'sr',
		'\\x25\\x21\\x50\\x53\\x2d\\x41\\x64\\x6f\\x62\\x65\\x2d\\x33\\x2e\\x30\\x20\\x45\\x50\\x53\\x46\\x2d\\x33\\x2e\\x30(\\x0d|\\x0a|\\x0d\\x0a|\\x0a\\x0d)',
		0,
		NULL,
		's',
		'',
		'eps',
		array(
			'eps',
			'epsf',
			'ps'
		),
		'application/postscript',
		'com.adobe.encapsulated-postscript',
		'Encapsulated PostScript File Format',
		'3',
		'EPS (3.0)',
		'fmt/124',
		NULL,
		'Page Description',
		'Full',
		'Encapsulated Postscript (EPS) is a page description format, developed by Adobe Systems Incorporated. Version 3, the third version of the format, was developed in 1992, and subsequently revised in 1999 to include a number of extensions and features of the PostScript language not previously documented. An EPS file comprises a header section followed by a PostScript document. The header can refer to version 3.0 or version 3.1. An EPS can optionally include a preview raster image, in which case a binary header is prepended to the header.',
		'Text',
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=331&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/Encapsulated_PostScript',
			'https://web.archive.org/web/20170818010030if_/http://wwwimages.adobe.com/content/dam/Adobe/en/devnet/postscript/pdfs/5002.EPSF_Spec.pdf',
			'http://www.mjr19.org.uk/eps.pdf',
			'https://epsfile.top/'
		)
	),

	array(
		0,
		25,
		'sr',
		'\\x25\\x21\\x50\\x53\\x2d\\x41\\x64\\x6f\\x62\\x65\\x2d\\x33\\x2e\\x31\\x20\\x45\\x50\\x53\\x46\\x2d\\x33\\x2e\\x30(\\x0d|\\x0a|\\x0d\\x0a|\\x0a\\x0d)',
		0,
		NULL,
		's',
		'',
		'eps',
		array(
			'eps',
			'epsf',
			'ps'
		),
		'application/postscript',
		'com.adobe.encapsulated-postscript',
		'Encapsulated PostScript File Format',
		'3.1',
		'EPS (3.1)',
		'fmt/124',
		NULL,
		'Page Description',
		'Full',
		'Encapsulated Postscript (EPS) is a page description format, developed by Adobe Systems Incorporated. Version 3, the third version of the format, was developed in 1992, and subsequently revised in 1999 to include a number of extensions and features of the PostScript language not previously documented. An EPS file comprises a header section followed by a PostScript document. The header can refer to version 3.0 or version 3.1. An EPS can optionally include a preview raster image, in which case a binary header is prepended to the header.',
		'Text',
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=331&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/Encapsulated_PostScript',
			'https://web.archive.org/web/20170818010030if_/http://wwwimages.adobe.com/content/dam/Adobe/en/devnet/postscript/pdfs/5002.EPSF_Spec.pdf',
			'http://www.mjr19.org.uk/eps.pdf',
			'https://epsfile.top/'
		)
	),

	array(
		0,
		128,
		'sr',
		'\\x0a\\x00\\x01[\\x01\\x02\\x04\\x08][\\x00-\\xff]{60}\\x00[\\x00-\\xff]{3}[\\x01\\x02]\\x00[\\x00-\\xff]{4}\\x00{54}',
		0,
		NULL,
		's',
		'',
		'pcx',
		array(
			'pcx'
		),
		'image/vnd.zbrush.pcx',
		NULL,
		'PiCture eXchange Format (PCX)',
		'0',
		'ZSoft PC Paintbrush Bitmap (0)',
		'fmt/86',
		NULL,
		'Image (Raster)',
		NULL,
		'PCX, standing for PiCture eXchange, is an image file format developed by the now-defunct ZSoft Corporation of Marietta, Georgia, United States. It was the native file format for PC Paintbrush and became one of the first widely accepted DOS imaging standards, although it has since been succeeded by more sophisticated image formats, such as BMP, JPEG, and PNG. PCX files commonly stored palette-indexed images ranging from 2 or 4 colors to 16 and 256 colors, although the format has been extended to record true-color (24-bit) images as well.',
		'Binary',
		'Little-endian (Intel)',
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=621&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/PCX',
			'http://www.shikadi.net/moddingwiki/PCX_Format',
			'http://bespin.org/~qz/pc-gpe/pcx.txt'
		)
	),

	array(
		0,
		128,
		'sr',
		'\\x0a\\x02\\x01[\\x01\\x02\\x04\\x08][\\x00-\\xff]{60}\\x00[\\x00-\\xff]{3}[\\x01\\x02]\\x00[\\x00-\\xff]{4}\\x00{54}',
		0,
		NULL,
		's',
		'',
		'pcx',
		array(
			'pcx'
		),
		'image/vnd.zbrush.pcx',
		NULL,
		'PiCture eXchange Format (PCX)',
		'2',
		'ZSoft PC Paintbrush Bitmap (2)',
		'fmt/87',
		NULL,
		'Image (Raster)',
		NULL,
		'PCX, standing for PiCture eXchange, is an image file format developed by the now-defunct ZSoft Corporation of Marietta, Georgia, United States. It was the native file format for PC Paintbrush and became one of the first widely accepted DOS imaging standards, although it has since been succeeded by more sophisticated image formats, such as BMP, JPEG, and PNG. PCX files commonly stored palette-indexed images ranging from 2 or 4 colors to 16 and 256 colors, although the format has been extended to record true-color (24-bit) images as well.',
		'Binary',
		'Little-endian (Intel)',
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=622&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/PCX',
			'http://www.shikadi.net/moddingwiki/PCX_Format',
			'http://bespin.org/~qz/pc-gpe/pcx.txt'
		)
	),

	array(
		0,
		128,
		'sr',
		'\\x0a\\x03\\x01[\\x01\\x02\\x04\\x08][\\x00-\\xff]{60}\\x00[\\x00-\\xff]{3}[\\x01\\x02]\\x00[\\x00-\\xff]{4}\\x00{54}',
		0,
		NULL,
		's',
		'',
		'pcx',
		array(
			'pcx'
		),
		'image/vnd.zbrush.pcx',
		NULL,
		'PiCture eXchange Format (PCX)',
		'3',
		'ZSoft PC Paintbrush Bitmap (3)',
		'fmt/88',
		NULL,
		'Image (Raster)',
		NULL,
		'PCX, standing for PiCture eXchange, is an image file format developed by the now-defunct ZSoft Corporation of Marietta, Georgia, United States. It was the native file format for PC Paintbrush and became one of the first widely accepted DOS imaging standards, although it has since been succeeded by more sophisticated image formats, such as BMP, JPEG, and PNG. PCX files commonly stored palette-indexed images ranging from 2 or 4 colors to 16 and 256 colors, although the format has been extended to record true-color (24-bit) images as well.',
		'Binary',
		'Little-endian (Intel)',
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=623&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/PCX',
			'http://www.shikadi.net/moddingwiki/PCX_Format',
			'http://bespin.org/~qz/pc-gpe/pcx.txt'
		)
	),

	array(
		0,
		128,
		'sr',
		'\\x0a\\x04\\x01[\\x01\\x02\\x04\\x08][\\x00-\\xff]{60}\\x00[\\x00-\\xff]{3}[\\x01\\x02]\\x00[\\x00-\\xff]{4}\\x00{54}',
		0,
		NULL,
		's',
		'',
		'pcx',
		array(
			'pcx'
		),
		'image/vnd.zbrush.pcx',
		NULL,
		'PiCture eXchange Format (PCX)',
		'4',
		'ZSoft PC Paintbrush Bitmap (4)',
		'fmt/89',
		NULL,
		'Image (Raster)',
		NULL,
		'PCX, standing for PiCture eXchange, is an image file format developed by the now-defunct ZSoft Corporation of Marietta, Georgia, United States. It was the native file format for PC Paintbrush and became one of the first widely accepted DOS imaging standards, although it has since been succeeded by more sophisticated image formats, such as BMP, JPEG, and PNG. PCX files commonly stored palette-indexed images ranging from 2 or 4 colors to 16 and 256 colors, although the format has been extended to record true-color (24-bit) images as well.',
		'Binary',
		'Little-endian (Intel)',
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=624&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/PCX',
			'http://www.shikadi.net/moddingwiki/PCX_Format',
			'http://bespin.org/~qz/pc-gpe/pcx.txt'
		)
	),

	array(
		0,
		128,
		'sr',
		'\\x0a\\x05\\x01[\\x01\\x02\\x04\\x08][\\x00-\\xff]{60}[\\x00\\x20][\\x00-\\xff]{3}[\\x00\\x01\\x02][\\x00-\\xff]{5}(\\x00{54}|\\x20{54})',
		0,
		NULL,
		's',
		'',
		'pcx',
		array(
			'pcx'
		),
		'image/vnd.zbrush.pcx',
		NULL,
		'PiCture eXchange Format (PCX)',
		'5',
		'ZSoft PC Paintbrush Bitmap (5)',
		'fmt/90',
		NULL,
		'Image (Raster)',
		NULL,
		'PCX, standing for PiCture eXchange, is an image file format developed by the now-defunct ZSoft Corporation of Marietta, Georgia, United States. It was the native file format for PC Paintbrush and became one of the first widely accepted DOS imaging standards, although it has since been succeeded by more sophisticated image formats, such as BMP, JPEG, and PNG. PCX files commonly stored palette-indexed images ranging from 2 or 4 colors to 16 and 256 colors, although the format has been extended to record true-color (24-bit) images as well.',
		'Binary',
		'Little-endian (Intel)',
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=625&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/PCX',
			'http://www.shikadi.net/moddingwiki/PCX_Format',
			'http://bespin.org/~qz/pc-gpe/pcx.txt'
		)
	),

	array(
		1,
		16,
		'sr',
		'\\x01[\\x01\\x09][\\x00-\\xff]{4}[\\x0f\\x10\\x18\\x20][\\x00-\\xff]{8}[\\x08\\x0f\\x10\\x18\\x20]',
		0,
		NULL,
		's',
		'',
		'tga',
		array(
			'afi',
			'bpx',
			'icb',
			'tga',
			'vda',
			'vst'
		),
		'image/x-tga',
		'com.truevision.tga-image',
		'Truevision TGA Bitmap',
		'1.0 (BOF 1)',
		'TGA, Targa Bitmap',
		'x-fmt/367',
		NULL,
		'Image (Raster)',
		NULL,
		'The Truevision TGA File Format comprises 5 areas, each of which contains one or more fields of fixed or variable length. The 5 file areas are: (1) TGA File Header, (2) Image/Color Map Data, (3) Developer Area, (4) Extension Area and (5) TGA File Footer. The last 3 areas, the Developer Area, the Extension Area and the TGA File Footer are new to the file specification as of September, 1989. For this reason, images created with software written before September, 1989 will probably not contain these three fields.',
		'Text',
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=533&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/Truevision_TGA',
			'http://www.dca.fee.unicamp.br/~martino/disciplinas/ea978/tgaffs.pdf'
		)
	),

	array(
		1,
		16,
		'sr',
		'\\x00[\\x02\\x03\\x0a\\x0b]\\x00\\x00\\x00\\x00\\x00[\\x00-\\xff]{8}[\\x08\\x0f\\x10\\x18\\x20]',
		0,
		NULL,
		's',
		'',
		'tga',
		array(
			'afi',
			'bpx',
			'icb',
			'tga',
			'vda',
			'vst'
		),
		'image/x-tga',
		'com.truevision.tga-image',
		'Truevision TGA Bitmap',
		'1.0 (BOF 2)',
		'TGA, Targa Bitmap',
		'x-fmt/367',
		NULL,
		'Image (Raster)',
		NULL,
		'The Truevision TGA File Format comprises 5 areas, each of which contains one or more fields of fixed or variable length. The 5 file areas are: (1) TGA File Header, (2) Image/Color Map Data, (3) Developer Area, (4) Extension Area and (5) TGA File Footer. The last 3 areas, the Developer Area, the Extension Area and the TGA File Footer are new to the file specification as of September, 1989. For this reason, images created with software written before September, 1989 will probably not contain these three fields.',
		'Text',
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=533&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/Truevision_TGA',
			'http://www.dca.fee.unicamp.br/~martino/disciplinas/ea978/tgaffs.pdf'
		)
	),

	array(
		0,
		NULL,
		'h',
		'54525545564953494f4e2d5846494c452e00',
		0,
		NULL,
		's',
		'',
		'tga',
		array(
			'icb',
			'tga',
			'vda',
			'vst'
		),
		'image/x-tga',
		'com.truevision.tga-image',
		'Truevision TGA Bitmap',
		'2.0',
		NULL,
		'fmt/402',
		NULL,
		'Image (Raster)',
		NULL,
		'The Truevision TGA File Format comprises 5 areas, each of which contains one or more fields of fixed or variable length. The 5 file areas are: (1) TGA File Header, (2) Image/Color Map Data, (3) Developer Area, (4) Extension Area and (5) TGA File Footer. The last 3 areas, the Developer Area, the Extension Area and the TGA File Footer are new to the file specification as of September, 1989. For this reason, images created with software written before September, 1989 will probably not contain these three fields.',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=1150&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/Truevision_TGA',
			'http://www.dca.fee.unicamp.br/~martino/disciplinas/ea978/tgaffs.pdf'
		)
	),

	array(
		8,
		66,
		'sr',
		'\\x01\\x00\\x00\\x00[\\x00-\\xff]{22}\\x4c\\x50[\\x00-\\xff]{36}\\x00\\x00',
		0,
		NULL,
		's',
		'',
		'eot',
		array(
			'eot'
		),
		'application/vnd.ms-fontobject',
		NULL,
		'Embedded OpenType (EOT) File Format',
		'0x00010000',
		NULL,
		'fmt/1382',
		NULL,
		'Font',
		NULL,
		'The Embedded OpenType File Format (EOT) was developed by Microsoft to enable TrueType and OpenType fonts to be linked to web pages for download to render the web page with the font the author desired - https://www.w3.org/Submission/2008/SUBM-EOT-20080305/',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=2200&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/Embedded_OpenType',
			'https://www.w3.org/Submission/EOT/#FileFormat'
		)
	),

	array(
		8,
		66,
		'sr',
		'\\x01\\x00\\x02\\x00[\\x00-\\xff]{22}\\x4c\\x50[\\x00-\\xff]{36}\\x00\\x00',
		0,
		NULL,
		's',
		'',
		'eot',
		array(
			'eot'
		),
		'application/vnd.ms-fontobject',
		NULL,
		'Embedded OpenType (EOT) File Format',
		'0x00010002',
		NULL,
		NULL,
		NULL,
		'Font',
		NULL,
		'The Embedded OpenType File Format (EOT) was developed by Microsoft to enable TrueType and OpenType fonts to be linked to web pages for download to render the web page with the font the author desired - https://www.w3.org/Submission/2008/SUBM-EOT-20080305/',
		NULL,
		NULL,
		array(
			'https://en.wikipedia.org/wiki/Embedded_OpenType',
			'https://www.w3.org/Submission/EOT/#FileFormat'
		)
	),

	array(
		8,
		66,
		'sr',
		'\\x02\\x00\\x01\\x00[\\x00-\\xff]{22}\\x4c\\x50[\\x00-\\xff]{36}\\x00\\x00',
		0,
		NULL,
		's',
		'',
		'eot',
		array(
			'eot'
		),
		'application/vnd.ms-fontobject',
		NULL,
		'Embedded OpenType (EOT) File Format',
		'0x00020001',
		NULL,
		'fmt/1383',
		NULL,
		'Font',
		NULL,
		'The Embedded OpenType File Format (EOT) was developed by Microsoft to enable TrueType and OpenType fonts to be linked to web pages for download to render the web page with the font the author desired - https://www.w3.org/Submission/2008/SUBM-EOT-20080305/',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=2201&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/Embedded_OpenType',
			'https://www.w3.org/Submission/EOT/#FileFormat'
		)
	),

	array(
		8,
		66,
		'sr',
		'\\x02\\x00\\x02\\x00[\\x00-\\xff]{22}\\x4c\\x50[\\x00-\\xff]{36}\\x00\\x00',
		0,
		NULL,
		's',
		'',
		'eot',
		array(
			'eot'
		),
		'application/vnd.ms-fontobject',
		NULL,
		'Embedded OpenType (EOT) File Format',
		'0x00020002',
		NULL,
		'fmt/1384',
		NULL,
		'Font',
		NULL,
		'The Embedded OpenType File Format (EOT) was developed by Microsoft to enable TrueType and OpenType fonts to be linked to web pages for download to render the web page with the font the author desired - https://www.w3.org/Submission/2008/SUBM-EOT-20080305/',
		NULL,
		NULL,
		array(
			'https://www.nationalarchives.gov.uk/PRONOM/Format/proFormatSearch.aspx?status=detailReport&id=2202&strPageToDisplay=summary',
			'https://en.wikipedia.org/wiki/Embedded_OpenType',
			'https://www.w3.org/Submission/EOT/#FileFormat'
		)
	),

);
