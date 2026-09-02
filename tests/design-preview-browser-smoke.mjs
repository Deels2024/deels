import { chromium } from 'playwright';
import fs from 'node:fs';
import path from 'node:path';

const baseUrl = process.env.QA_BASE_URL || 'http://127.0.0.1:4173';
const outputDir = process.env.QA_OUTPUT_DIR || path.resolve('qa-output');
fs.mkdirSync(outputDir, { recursive: true });

const screens = ['home', 'feed', 'challenges', 'battles', 'profile', 'wallet', 'messages', 'create', 'campaign', 'admin'];
const viewports = [
  { name: 'mobile-375', width: 375, height: 812 },
  { name: 'mobile-430', width: 430, height: 932 },
  { name: 'tablet-768', width: 768, height: 1024 },
  { name: 'desktop-1280', width: 1280, height: 900 },
  { name: 'desktop-1440', width: 1440, height: 1000 },
];

const report = [];
let failed = false;
const browser = await chromium.launch({ headless: true });

try {
  for (const viewport of viewports) {
    const context = await browser.newContext({
      viewport: { width: viewport.width, height: viewport.height },
      deviceScaleFactor: 1,
      colorScheme: 'light',
    });
    const page = await context.newPage();

    for (const screen of screens) {
      const runtimeErrors = [];
      const consoleErrors = [];
      const onPageError = (error) => runtimeErrors.push(String(error?.message || error));
      const onConsole = (message) => {
        if (message.type() === 'error') consoleErrors.push(message.text());
      };
      page.on('pageerror', onPageError);
      page.on('console', onConsole);

      await page.goto(`${baseUrl}/#${screen}`, { waitUntil: 'networkidle' });
      await page.evaluate(async () => {
        if (document.fonts?.ready) await document.fonts.ready;
      });
      await page.waitForTimeout(180);

      const metrics = await page.evaluate((screenName) => {
        const active = document.querySelector(`#screen-${screenName}`);
        const activeScreens = [...document.querySelectorAll('.screen.active')].map((node) => node.id);
        const root = document.documentElement;
        const overflow = root.scrollWidth - root.clientWidth;
        const fontFamily = getComputedStyle(document.body).fontFamily;

        const offenders = [...document.querySelectorAll('body *')]
          .filter((node) => {
            const style = getComputedStyle(node);
            if (style.display === 'none' || style.visibility === 'hidden' || style.position === 'fixed') return false;
            const rect = node.getBoundingClientRect();
            if (rect.width <= 0 || rect.height <= 0) return false;
            return rect.right > root.clientWidth + 3 || rect.left < -3;
          })
          .slice(0, 8)
          .map((node) => ({
            tag: node.tagName.toLowerCase(),
            className: String(node.className || '').slice(0, 120),
            left: Math.round(node.getBoundingClientRect().left),
            right: Math.round(node.getBoundingClientRect().right),
          }));

        const controls = [...document.querySelectorAll('button, a.button, a.pbtn, [role="button"]')]
          .filter((node) => {
            const style = getComputedStyle(node);
            const rect = node.getBoundingClientRect();
            return style.display !== 'none' && style.visibility !== 'hidden' && rect.width > 0 && rect.height > 0;
          });
        const smallControls = controls
          .map((node) => {
            const rect = node.getBoundingClientRect();
            return {
              tag: node.tagName.toLowerCase(),
              className: String(node.className || '').slice(0, 100),
              text: (node.textContent || '').trim().replace(/\s+/g, ' ').slice(0, 70),
              width: Math.round(rect.width),
              height: Math.round(rect.height),
            };
          })
          .filter((item) => item.width < 32 || item.height < 32)
          .slice(0, 8);

        const h1 = active?.querySelector('h1');
        const h1Size = h1 ? parseFloat(getComputedStyle(h1).fontSize) : null;
        const visible = !!active && getComputedStyle(active).display !== 'none' && active.getBoundingClientRect().height > 0;

        return {
          activeScreens,
          visible,
          overflow,
          offenders,
          smallControls,
          fontFamily,
          h1Size,
          bodyWidth: root.clientWidth,
          pageWidth: root.scrollWidth,
        };
      }, screen);

      const problems = [];
      if (!metrics.visible) problems.push('active screen is not visible');
      if (metrics.activeScreens.length !== 1 || metrics.activeScreens[0] !== `screen-${screen}`) {
        problems.push(`unexpected active screens: ${metrics.activeScreens.join(', ')}`);
      }
      if (metrics.overflow > 3) problems.push(`horizontal overflow ${metrics.overflow}px`);
      if (!/Gilroy/i.test(metrics.fontFamily)) problems.push(`Gilroy is not active: ${metrics.fontFamily}`);
      if (metrics.smallControls.length) problems.push(`${metrics.smallControls.length} controls smaller than 32px`);
      if (runtimeErrors.length) problems.push(`${runtimeErrors.length} page errors`);
      if (consoleErrors.length) problems.push(`${consoleErrors.length} console errors`);
      if (metrics.h1Size !== null && metrics.h1Size < 28) problems.push(`h1 too small: ${metrics.h1Size}px`);

      const entry = {
        viewport: viewport.name,
        width: viewport.width,
        height: viewport.height,
        screen,
        problems,
        runtimeErrors,
        consoleErrors,
        ...metrics,
      };
      report.push(entry);

      if (problems.length) {
        failed = true;
        console.error(`[FAIL] ${viewport.name} / ${screen}: ${problems.join('; ')}`);
        if (metrics.offenders.length) console.error('  overflow offenders:', metrics.offenders);
        if (metrics.smallControls.length) console.error('  small controls:', metrics.smallControls);
        if (runtimeErrors.length) console.error('  page errors:', runtimeErrors);
        if (consoleErrors.length) console.error('  console errors:', consoleErrors);
      } else {
        console.log(`[OK] ${viewport.name} / ${screen}`);
      }

      const screenshotName = `${viewport.name}--${screen}.jpg`;
      await page.screenshot({
        path: path.join(outputDir, screenshotName),
        type: 'jpeg',
        quality: 72,
        fullPage: true,
      });

      page.off('pageerror', onPageError);
      page.off('console', onConsole);
    }

    await context.close();
  }
} finally {
  await browser.close();
}

fs.writeFileSync(path.join(outputDir, 'responsive-report.json'), JSON.stringify(report, null, 2));

const summary = viewports.map((viewport) => {
  const rows = report.filter((row) => row.viewport === viewport.name);
  return `${viewport.name}: ${rows.filter((row) => row.problems.length === 0).length}/${rows.length} screens passed`;
});
fs.writeFileSync(path.join(outputDir, 'summary.txt'), `${summary.join('\n')}\n`);
console.log(summary.join('\n'));

if (failed) process.exit(1);
