const puppeteer = require('puppeteer');
const moment = require('moment');
const fs = require('fs');

(async () => {

  if (!fs.existsSync('screenshots')) {
    fs.mkdirSync('screenshots');
  }

  const browser = await puppeteer.launch()
  const page = await browser.newPage()
  const navigationPromise = page.waitForNavigation()

  await page.setViewport({ width: 1920, height: 969 })

  await login(page, navigationPromise);
  await addMiQuizActivity(page, {
    course: 2,
    section: 0,
    title: 'Test 03',
    shortTitle: 'T03',
  }, navigationPromise);

  await browser.close()

  async function login(page, navigationPromise) {
    await page.goto('http://localhost:3000/login/index.php');
    await navigationPromise;

    await enterValue('#username', 'bob');
    await enterValue('#password', 'Bob1234.');
    await click('#loginbtn');
    await navigationPromise;
  }

  async function addMiQuizActivity(page, config, navigationPromise) {

    const { title, shortTitle, course, section } = config;
    await page.goto(`http://localhost:3000/course/modedit.php?add=miquiz&type=&course=${course}&section=${section}&return=0&sr=0`)
    await navigationPromise

    await enterValue('#id_name', title);
    await enterValue('#id_short_name', shortTitle);

    const tomorrow = moment().add(1, 'd');
    const dayAfterTomorrow = moment().add(2, 'd');

    await selectValue('#id_timeuntilproductive_day', (tomorrow.date()).toString());
    await selectValue('#id_timeuntilproductive_month', (tomorrow.month() + 1).toString());

    await selectValue('#id_assesstimefinish_day', (dayAfterTomorrow.date()).toString());
    await selectValue('#id_assesstimefinish_month', (dayAfterTomorrow.month() + 1).toString());

    await click('.categorycheckbox');
    await page.screenshot({ path: `screenshots/activity_${shortTitle}_form.png` });

    await click('#id_submitbutton2');

    await navigationPromise
    await page.screenshot({ path: `screenshots/activity_${shortTitle}_ready.png` });
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
    await page.waitForSelector(selector);
    await page.click(selector);
  }
})()