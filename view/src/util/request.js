import {API_BASE} from "../config";

export default (path, method = 'POST', data = {}) => {
    return new Promise((resolve, reject) => {
        uni.request({
            url: API_BASE + path, //仅为示例，并非真实接口地址。
            data: data,
            method: method,
            success: (res) => {
                resolve(res.data);
                // console.log(res.data);
                // this.text = 'request success';
            },
            fail: (err) => {
                reject(err);
                // console.log(err);
                // this.text = 'request fail';
            }
        });
    })

}
