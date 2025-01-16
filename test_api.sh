#!/bin/bash

# 设置基础 URL
BASE_URL="http://localhost:8080/api"
TOKEN=""
TEST_USER_ID=""

# 生成随机用户名和邮箱
RANDOM_USER="test_user_$(date +%s)"
RANDOM_EMAIL="test_${RANDOM_USER}@example.com"

# 颜色定义
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

# 测试函数
test_api() {
    local method=$1
    local endpoint=$2
    local data=$3
    local description=$4
    local auth=${5:-true}

    echo -e "\n${YELLOW}测试: ${description}${NC}"
    echo "请求: ${method} ${endpoint}"
    
    if [ -n "$data" ]; then
        echo "数据: ${data}"
    fi

    local curl_cmd="curl -s -X ${method}"
    if [ -n "$data" ]; then
        curl_cmd="${curl_cmd} -H \"Content-Type: application/json\" -d '${data}'"
    fi
    if [ "$auth" = true ] && [ -n "$TOKEN" ]; then
        curl_cmd="${curl_cmd} -H \"Authorization: Bearer ${TOKEN}\""
    fi
    curl_cmd="${curl_cmd} ${BASE_URL}${endpoint}"

    # 将响应保存到临时文件
    local temp_file=$(mktemp)
    eval "${curl_cmd}" > "${temp_file}"
    local response=$(<"${temp_file}")
    rm "${temp_file}"

    # 检查响应是否为空
    if [ -z "$response" ]; then
        echo -e "${RED}✗ 测试失败${NC}"
        echo -e "错误: ${RED}服务器没有响应${NC}"
        return 1
    fi

    # 检查响应是否为有效的 JSON
    if ! echo "$response" | jq . >/dev/null 2>&1; then
        echo -e "${RED}✗ 测试失败${NC}"
        echo -e "错误: ${RED}响应不是有效的 JSON${NC}"
        echo "响应内容: $response"
        return 1
    fi

    # 使用 jq 解析 JSON 响应
    local success=$(echo "$response" | jq -r '.success')
    local message=$(echo "$response" | jq -r '.message // empty')

    if [ "$success" = "true" ]; then
        echo -e "${GREEN}✓ 测试通过${NC}"
        if [ "$endpoint" = "/login" ]; then
            TOKEN=$(echo "$response" | jq -r '.token')
            echo -e "Token: ${TOKEN:0:20}..."
        elif [ "$method" = "POST" ] && [ "$endpoint" = "/users" ]; then
            TEST_USER_ID=$(echo "$response" | jq -r '.data.id')
            echo -e "创建的用户 ID: ${TEST_USER_ID}"
        fi
        if [ -n "$message" ]; then
            echo -e "消息: ${GREEN}${message}${NC}"
        fi
        return 0
    else
        echo -e "${RED}✗ 测试失败${NC}"
        if [ -n "$message" ]; then
            echo -e "错误: ${RED}${message}${NC}"
        else
            echo -e "错误: ${RED}未知错误${NC}"
            echo "完整响应:"
            echo "$response" | jq .
        fi
        return 1
    fi
}

# 检查 jq 是否安装
if ! command -v jq &> /dev/null; then
    echo -e "${RED}错误: 需要安装 jq 来解析 JSON 响应${NC}"
    echo "请运行: sudo apt-get install jq"
    exit 1
fi

echo -e "${YELLOW}开始 API 测试...${NC}"

# 1. 测试登录
test_api "POST" "/login" '{"username":"admin","password":"admin123"}' "管理员登录" false
if [ $? -ne 0 ]; then
    echo -e "${RED}登录失败，终止测试${NC}"
    exit 1
fi

sleep 1

# 2. 测试获取用户列表
test_api "GET" "/users" "" "获取用户列表"

sleep 1

# 3. 测试创建用户
test_api "POST" "/users" "{\"username\":\"$RANDOM_USER\",\"password\":\"test123\",\"email\":\"$RANDOM_EMAIL\",\"role\":\"user\"}" "创建新用户"
if [ $? -ne 0 ]; then
    echo -e "${RED}创建用户失败，跳过相关测试${NC}"
    TEST_USER_ID=""
fi

sleep 1

# 4. 测试获取角色列表
test_api "GET" "/roles" "" "获取角色列表"

sleep 1

# 5. 测试获取权限列表
test_api "GET" "/permissions" "" "获取权限列表"

sleep 1

# 6. 测试获取日志列表
test_api "GET" "/logs" "" "获取日志列表"

sleep 1

# 7. 测试更新用户
if [ -n "$TEST_USER_ID" ]; then
    test_api "PUT" "/users/${TEST_USER_ID}" '{"email":"updated@example.com"}' "更新用户信息"
    sleep 1
else
    echo -e "\n${YELLOW}跳过: 更新用户（没有可用的测试用户）${NC}"
fi

# 8. 测试删除用户
if [ -n "$TEST_USER_ID" ]; then
    test_api "DELETE" "/users/${TEST_USER_ID}" "" "删除用户"
    sleep 1
else
    echo -e "\n${YELLOW}跳过: 删除用户（没有可用的测试用户）${NC}"
fi

# 9. 测试退出登录
test_api "POST" "/logout" "" "退出登录"

echo -e "\n${GREEN}API 测试完成${NC}"

# 清理临时文件
rm -f /tmp/tmp.* 