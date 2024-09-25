const formEl = document.querySelector('.reg-form');
const fromErr = document.querySelector('.from-reg-err')
const emailEl = document.querySelector('.email-reg');
const passEl = document.querySelector('.password-reg');
const repeatEl = document.querySelector('.repeat-reg');
const emailErr = document.querySelector('.email-reg-err')
const passErr = document.querySelector('.password-reg-err')
const repeatErr = document.querySelector('.repeat-reg-err')

function hasNumber(str){
    const pattern = /\d/;
    return pattern.test(str);
}

function accoundExists(q){
   return false ;
}
emailEl.addEventListener('change',()=>{
    //check if the email is in use
})

passEl.addEventListener('input',()=>{
    if(passEl.value.trim().length<7)return passErr.textContent="Password is too short!";
    if(passEl.value.trim().length>50)return passErr.textContent="Password is too long!";
    if(!hasNumber(passEl.value.trim()))return passErr.textContent="Password must contain at least one number!";
    passErr.textContent=""
})

repeatEl.addEventListener('input',()=>{
    if(repeatEl.value!==passEl.value){
        return repeatErr.textContent="Passwords do not match!";
    }
    repeatErr.textContent="";
})

formEl.addEventListener('submit',(e)=>{
    e.preventDefault()
    let email = passEl.value.trim();
    let pass = passEl.value.trim();
    let repeat = repeatEl.value.trim();
    if(accoundExists(email)||pass.length<7||pass.length>50||!hasNumber(pass)||repeat!==pass)return fromErr.textContent="1 or more Inputs are invalid";
    formEl.submit()
})