import { KinCore } from '@kin/core';

window.testEcho = () => {
    const inputValue = document.getElementById("echoInput").value;
    KinCore.echo({ value: inputValue })
}
