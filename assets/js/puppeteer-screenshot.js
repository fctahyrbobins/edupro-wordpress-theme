const puppeteer = require('puppeteer');

// Get command line arguments
const [, , url, outputPath] = process.argv;

if (!url || !outputPath) {
    console.error('Usage: node puppeteer-screenshot.js <url> <outputPath>');
    process.exit(1);
}

(async () => {
    try {
        // Launch browser
        const browser = await puppeteer.launch({
            headless: true,
            args: [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--disable-dev-shm-usage',
                '--disable-accelerated-2d-canvas',
                '--disable-gpu'
            ]
        });

        // Create new page
        const page = await browser.newPage();

        // Set viewport size
        await page.setViewport({
            width: 1200,
            height: 800,
            deviceScaleFactor: 1
        });

        // Navigate to URL
        await page.goto(url, {
            waitUntil: ['load', 'networkidle0'],
            timeout: 30000
        });

        // Wait for any animations to complete
        await page.evaluate(() => {
            return new Promise((resolve) => {
                // Check if any animations are running
                const checkAnimations = () => {
                    const animations = document.getAnimations();
                    if (animations.length === 0) {
                        resolve();
                    } else {
                        requestAnimationFrame(checkAnimations);
                    }
                };
                checkAnimations();
            });
        });

        // Ensure all images are loaded
        await page.evaluate(async () => {
            const selectors = Array.from(document.getElementsByTagName('img'));
            await Promise.all(selectors.map(img => {
                if (img.complete) return;
                return new Promise((resolve, reject) => {
                    img.addEventListener('load', resolve);
                    img.addEventListener('error', reject);
                });
            }));
        });

        // Get page height
        const bodyHandle = await page.$('body');
        const { height } = await bodyHandle.boundingBox();
        await bodyHandle.dispose();

        // Update viewport height to match content
        await page.setViewport({
            width: 1200,
            height: Math.ceil(height),
            deviceScaleFactor: 1
        });

        // Take screenshot
        await page.screenshot({
            path: outputPath,
            fullPage: true,
            type: 'png',
            omitBackground: false,
            encoding: 'binary'
        });

        // Close browser
        await browser.close();

        console.log('Screenshot saved successfully');
        process.exit(0);

    } catch (error) {
        console.error('Error generating screenshot:', error);
        process.exit(1);
    }
})();

// Error handling for unhandled rejections
process.on('unhandledRejection', (error) => {
    console.error('Unhandled rejection:', error);
    process.exit(1);
});

// Handle cleanup on process termination
async function cleanup(browser) {
    if (browser) {
        try {
            await browser.close();
        } catch (error) {
            console.error('Error closing browser:', error);
        }
    }
    process.exit();
}

// Handle process termination signals
['SIGINT', 'SIGTERM', 'SIGQUIT'].forEach(signal => {
    process.on(signal, () => {
        console.log(`\nReceived ${signal}, cleaning up...`);
        cleanup();
    });
});

// Additional error handling functions
function handlePageError(error) {
    console.error('Page error:', error);
}

function handleConsoleMessage(msg) {
    const type = msg.type();
    const text = msg.text();
    
    // Log console messages from the page
    if (type === 'error') {
        console.error('Page console error:', text);
    } else if (type === 'warning') {
        console.warn('Page console warning:', text);
    } else {
        console.log('Page console:', text);
    }
}

// Helper function to wait for network idle
async function waitForNetworkIdle(page, timeout = 30000, maxInflightRequests = 0) {
    try {
        await page.waitForFunction(
            `window.performance.getEntriesByType('resource').length === window._resourceCount`,
            { timeout }
        );
    } catch (error) {
        console.warn('Timeout waiting for network idle');
    }
}

// Helper function to check if element is visible
async function isElementVisible(page, selector) {
    try {
        const element = await page.$(selector);
        if (!element) {
            return false;
        }
        
        const visible = await page.evaluate(el => {
            const style = window.getComputedStyle(el);
            return style && style.display !== 'none' && style.visibility !== 'hidden' && style.opacity !== '0';
        }, element);
        
        return visible;
    } catch (error) {
        console.error('Error checking element visibility:', error);
        return false;
    }
}

// Helper function to wait for fonts to load
async function waitForFonts(page) {
    try {
        await page.evaluate(() => {
            return document.fonts.ready;
        });
    } catch (error) {
        console.warn('Error waiting for fonts:', error);
    }
}

// Helper function to ensure page is ready
async function ensurePageReady(page) {
    try {
        // Wait for DOM content
        await page.waitForFunction('document.readyState === "complete"');
        
        // Wait for fonts
        await waitForFonts(page);
        
        // Wait for network idle
        await waitForNetworkIdle(page);
        
        // Wait a bit more for any final rendering
        await page.waitForTimeout(1000);
    } catch (error) {
        console.error('Error ensuring page ready:', error);
        throw error;
    }
}
