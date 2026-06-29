import { KinNetwork } from '@kin/network';

window.testEcho = () => {
    const inputValue = document.getElementById("echoInput").value;
    KinNetwork.echo({ value: inputValue })
}
