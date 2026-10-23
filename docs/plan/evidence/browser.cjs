const {chromium, devices}=require('playwright');
const fs=require('fs');
(async()=>{
 const browser=await chromium.launch({headless:true});
 const mobile=process.argv.includes('--mobile');
 const context=await browser.newContext(mobile?devices['iPhone 13']:{viewport:{width:1100,height:1000}});
 const page=await context.newPage();
 const base=__dirname;
 let results=[];
 for(const mileage of mobile?[400001]:[399999,400000,400001,600000]){
  await page.goto('http://127.0.0.1:8080/');
  await page.locator('#mileage').fill(String(mileage));
  const responsePromise=page.waitForResponse(r=>r.url().endsWith('/api/applications')&&r.request().method()==='POST');
  await page.getByRole('button',{name:'Отправить заявку',exact:true}).click();
  const response=await responsePromise;
  const json=await response.json();
  await page.waitForFunction(()=>!document.querySelector('#result').hidden||!document.querySelector('#errors').hidden);
  const text=await page.locator(response.ok()?'#result':'#errors').innerText();
  const file=`${mobile?'mobile-':''}${mileage}.png`;
  await page.screenshot({path:`${base}/${file}`,fullPage:true});
  results.push({mileage,status:response.status(),json,text,screenshot:file});
 }
 fs.writeFileSync(`${base}/${mobile?'mobile':'desktop'}-results.json`,JSON.stringify(results,null,2));
 console.log(JSON.stringify(results,null,2));await browser.close();
})();
