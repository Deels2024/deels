const fs = require('fs');
const path = require('path');

const root = path.resolve(__dirname, '..');
const publicDir = path.join(root, 'public');

const files = [
  'dist/css/owl.carousel.min.css',
  'dist/css/owl.theme.default.min.css',
  'dist/css/font/stylesheet.css',
  'dist/css/magnific-popup.css',
  'dist/css/admin_style.css',
  'dist/css/kpromo_slider.css',
  'js/libs/fancybox/jquery.fancybox.min.css',
  'dist/css/admin-top.css',
  'dist/css/app.css',
  'dist/css/main.css',
];

const output = 'dist/css/app.min.css';
const isWatchMode = process.argv.includes('--watch');
const base64Chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';

function toPublicUrl(filePath) {
  return `/${path.relative(publicDir, filePath).split(path.sep).join('/')}`;
}

function shouldSkipUrl(url) {
  return /^(?:data:|https?:|\/\/|\/|#)/i.test(url);
}

function normalizeUrl(rawUrl, sourceFile) {
  const trimmed = rawUrl.trim();
  const quote = trimmed[0] === '"' || trimmed[0] === "'" ? trimmed[0] : '';
  const unquoted = quote ? trimmed.slice(1, -1) : trimmed;

  if (shouldSkipUrl(unquoted)) {
    return `url(${trimmed})`;
  }

  const [pathname, suffix = ''] = unquoted.split(/(?=[?#])/);
  const absoluteAssetPath = path.resolve(path.dirname(sourceFile), pathname);
  const publicUrl = toPublicUrl(absoluteAssetPath) + suffix;

  return `url("${publicUrl}")`;
}

function rewriteUrls(css, sourceFile) {
  return css.replace(/url\(([^)]+)\)/g, (_, rawUrl) => normalizeUrl(rawUrl, sourceFile));
}

function minifyLine(css) {
  return css
    .replace(/\s+/g, ' ')
    .replace(/\s*([{}:;,>~])\s*/g, '$1')
    .replace(/;}/g, '}')
    .trim();
}

const outputFile = path.join(publicDir, output);
const sourceMapFile = `${outputFile}.map`;

function toVlqSigned(value) {
  return value < 0 ? ((-value) << 1) + 1 : value << 1;
}

function encodeVlq(value) {
  let vlq = toVlqSigned(value);
  let encoded = '';

  do {
    let digit = vlq & 31;
    vlq >>>= 5;
    if (vlq > 0) {
      digit |= 32;
    }
    encoded += base64Chars[digit];
  } while (vlq > 0);

  return encoded;
}

function encodeSegment(values) {
  return values.map(encodeVlq).join('');
}

function createSourceMap(mappings) {
  let previousSource = 0;
  let previousOriginalLine = 0;
  let previousOriginalColumn = 0;

  const encodedMappings = mappings.map((mapping) => {
    const segment = encodeSegment([
      0,
      mapping.sourceIndex - previousSource,
      mapping.originalLine - previousOriginalLine,
      0 - previousOriginalColumn,
    ]);

    previousSource = mapping.sourceIndex;
    previousOriginalLine = mapping.originalLine;
    previousOriginalColumn = 0;

    return segment;
  }).join(';');

  return {
    version: 3,
    file: path.basename(outputFile),
    sourceRoot: '/',
    sources: files,
    sourcesContent: files.map((file) => fs.readFileSync(path.join(publicDir, file), 'utf8')),
    names: [],
    mappings: encodedMappings,
  };
}

function build() {
  const lines = [];
  const mappings = [];

  files.forEach((file, sourceIndex) => {
    const sourceFile = path.join(publicDir, file);

    if (!fs.existsSync(sourceFile)) {
      throw new Error(`CSS file not found: ${file}`);
    }

    const css = fs.readFileSync(sourceFile, 'utf8');
    rewriteUrls(css, sourceFile).split(/\r?\n/).forEach((line, originalLine) => {
      const minifiedLine = minifyLine(line);

      if (!minifiedLine) {
        return;
      }

      lines.push(minifiedLine);
      mappings.push({sourceIndex, originalLine});
    });
  });

  lines.push(`/*# sourceMappingURL=${path.basename(sourceMapFile)} */`);

  fs.writeFileSync(outputFile, `${lines.join('\n')}\n`);
  fs.writeFileSync(sourceMapFile, `${JSON.stringify(createSourceMap(mappings))}\n`);

  console.log(`[${new Date().toLocaleTimeString()}] Built ${toPublicUrl(outputFile)} and ${toPublicUrl(sourceMapFile)}`);
}

function watch() {
  let timer = null;

  const scheduleBuild = (file) => {
    clearTimeout(timer);
    timer = setTimeout(() => {
      try {
        console.log(`[${new Date().toLocaleTimeString()}] Changed ${file}`);
        build();
      } catch (error) {
        console.error(error.message);
      }
    }, 150);
  };

  files.forEach((file) => {
    const sourceFile = path.join(publicDir, file);

    if (!fs.existsSync(sourceFile)) {
      throw new Error(`CSS file not found: ${file}`);
    }

    fs.watch(sourceFile, {persistent: true}, () => scheduleBuild(file));
  });

  console.log(`Watching ${files.length} CSS files. Press Ctrl+C to stop.`);
}

build();

if (isWatchMode) {
  watch();
}
