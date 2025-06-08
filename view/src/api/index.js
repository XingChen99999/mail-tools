import request from "../util/request";

export const index = (data) => {
  return request("/api/email/index",'POST',data);
};

// generate
export const generate = () => {
  return request("/api/email/generate",'POST');
};

export const check = (email) => {
  return request("/api/email/checkEmail",'POST',{email});
};