import { KinDevice } from '@kin/device';

window.testEcho = () => {
    const inputValue = document.getElementById("echoInput").value;
    KinDevice.echo({ value: inputValue })
}
