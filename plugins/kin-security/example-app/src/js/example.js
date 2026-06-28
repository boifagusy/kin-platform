import { KinSecurity } from '@kin/security';

window.testEcho = () => {
    const inputValue = document.getElementById("echoInput").value;
    KinSecurity.echo({ value: inputValue })
}
