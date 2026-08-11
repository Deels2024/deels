const fs = require('fs');
const path = require('path');

const root = path.resolve(__dirname, '..');
const publicDir = path.join(root, 'public');

const files = [
  'js/jquery-3.6.0.min.js',
  'dist/js/nouislider.min.js',
  'dist/js/slick.min.js',
  'dist/js/owl.carousel.min.js',
  'dist/js/jquery.magnific-popup.min.js',
  'dist/js/jquery.mask.min.js',
  'dist/js/script.js',
  'dist/js/admin_scripts.js',
  'dist/js/app.js',
];

const output = 'dist/js/app.min.js';
const isWatchMode = process.argv.includes('--watch');
const base64Chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/';

function toPublicUrl(filePath) {
  return `/${path.relative(publicDir, filePath).split(path.sep).join('/')}`;
}

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

function stripSourceMapComments(js) {
  return js
    .replace(/\/\/# sourceMappingURL=.*$/gm, '')
    .replace(/\/\*# sourceMappingURL=[\s\S]*?\*\//g, '');
}

function createSourceMap(mappings) {
  let previousSource = 0;
  let previousOriginalLine = 0;
  let previousOriginalColumn = 0;

  const encodedMappings = mappings.map((mapping) => {
    if (mapping === null) {
      return '';
    }

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

const outputFile = path.join(publicDir, output);
const sourceMapFile = `${outputFile}.map`;

function build() {
  const lines = [];
  const mappings = [];

  files.forEach((file, sourceIndex) => {
    const sourceFile = path.join(publicDir, file);

    if (!fs.existsSync(sourceFile)) {
      throw new Error(`JS file not found: ${file}`);
    }

    const js = stripSourceMapComments(fs.readFileSync(sourceFile, 'utf8'));

    lines.push(`;\n/* ${file} */`);
    mappings.push(null, null);

    js.split(/\r?\n/).forEach((line, originalLine) => {
      lines.push(line);
      mappings.push({sourceIndex, originalLine});
    });
  });

  lines.push(`//# sourceMappingURL=${path.basename(sourceMapFile)}`);
  mappings.push(null);

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
      throw new Error(`JS file not found: ${file}`);
    }

    fs.watch(sourceFile, {persistent: true}, () => scheduleBuild(file));
  });

  console.log(`Watching ${files.length} JS files. Press Ctrl+C to stop.`);
}

build();

if (isWatchMode) {
  watch();
}
