import { KinHeartbeat } from '@kin/heartbeat';

window.testEcho = () => {
    const inputValue = document.getElementById("echoInput").value;
    KinHeartbeat.echo({ value: inputValue })
}
