const puppeteer = require('puppeteer');
const moment = require('moment');
const fs = require('fs');
const _ = require('underscore');

(async () => {

  if (!fs.existsSync('screenshots')) {
    fs.mkdirSync('screenshots');
  }

  const browser = await puppeteer.launch({
    headless: false,
    slowMo: 250,
  })
  const page = await browser.newPage()

  const navigationPromise = page.waitForNavigation()
  await page.setViewport({ width: 1920, height: 969 })

  await login();
  await playTrainingGame();

  await browser.close()

  async function login() {
    await page.goto('http://localhost:8000/login');
    await navigationPromise;

    // await enterValue('input[name="login"]', 'bob');
    // await enterValue('input[name="password"]', 'Bob1234.');
    await enterValue('input[name="login"]', 'alice');
    await enterValue('input[name="password"]', 'Alice123.');
    await page.screenshot({ path: `screenshots/login.png` });
    await click('button[type=submit]');
    await navigationPromise;
    await page.screenshot({ path: `screenshots/login_after.png` });
  }

  async function playTrainingGame() {
    await startNewGame();
    // await resumeGame();

    // Round 1
    await selectCategory();
    await answerQuestion();
    await answerQuestion();
    await answerQuestion();

    // Round 2
    await selectCategory();
    await answerQuestion();
    await answerQuestion();
    await answerQuestion();

    // Round 3
    await selectCategory();
    await answerQuestion();
    await answerQuestion();
    await answerQuestion();

    // Round 4
    await selectCategory();
    await answerQuestion();
    await answerQuestion();
    await answerQuestion();

    // Round 5
    await selectCategory();
    await answerQuestion();
    await answerQuestion();
    await answerQuestion();

    // Round 6
    await selectCategory();
    await answerQuestion();
    await answerQuestion();
    await answerQuestion();

  }

  async function startNewGame() {
    await page.goto('http://localhost:8000/games/create?mode=training');
    await navigationPromise
    await click('a.btn-info.btn-lg');
    await navigationPromise
  }

  async function resumeGame() {
    await click('.panel-success a.btn-block.btn-info')
    await navigationPromise
    await click('a.btn-info.btn-lg');
    await navigationPromise
  }

  async function selectCategory() {
    await page.screenshot({ path: `screenshots/game_category.png` });
    const cat = _.sample([
      '#cat1',
      '#cat2',
      '#cat3',
    ])
    await click(cat);
    await navigationPromise
  }

  async function answerQuestion() {
    await page.screenshot({ path: `screenshots/game_question.png` });
    await click('#possibilities a.possibility');
    await click('a.btn-success.btn-next-action');
    await navigationPromise
  }

  async function enterValue(selector, value) {
    await page.waitForSelector(selector);
    await page.type(selector, value);
  }

  async function selectValue(selector, value) {
    await page.waitForSelector(selector);
    await page.select(selector, value);
  }

  async function click(selector) {
    await page.waitForSelector(selector, { visible: true });
    await page.click(selector);
  }
})()