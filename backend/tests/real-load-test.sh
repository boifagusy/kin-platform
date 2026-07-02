#!/bin/bash

URL="http://127.0.0.1:8000"
REQUESTS=50
CONCURRENT=5
SUCCESS=0
FAILED=0
TIMES=()

echo "Starting load test..."
echo "Total requests: $REQUESTS"
echo "Concurrent: $CONCURRENT"
echo ""

# Function to test endpoint
test_endpoint() {
    local name=$1
    local endpoint=$2
    local method=$3
    local data=$4
    
    echo "Testing: $name"
    local start=$(date +%s%N)
    
    for i in $(seq 1 $REQUESTS); do
        if [ "$method" = "POST" ]; then
            curl -s -o /dev/null -w "%{http_code}\n" -X POST "$URL$endpoint" \
                -H "Content-Type: application/json" \
                -d "$data" &
        else
            curl -s -o /dev/null -w "%{http_code}" "$URL$endpoint" &
        fi
        
        if (( $i % $CONCURRENT == 0 )); then
            wait
        fi
    done
    wait
    
    local end=$(date +%s%N)
    local duration=$(( ($end - $start) / 1000000 ))
    echo "  Duration: ${duration}ms"
    echo "  Requests/sec: $(echo "scale=2; $REQUESTS / ($duration/1000)" | bc)"
    echo ""
}

# Test 1: Health endpoint
test_endpoint "Health Check" "/api/v1/health" "GET" ""

# Test 2: Login endpoint
test_endpoint "Login" "/api/v1/auth/login-pin" "POST" '{"phone":"+2348052692060","pin":"1234"}'

# Test 3: Queue job dispatch
echo "Testing: Queue Job Dispatch"
START=$(date +%s%N)
for i in $(seq 1 20); do
    php artisan tinker --execute="App\Jobs\SendCheckInReminderJob::dispatch(1);" 2>/dev/null
done
END=$(date +%s%N)
DURATION=$(( ($END - $START) / 1000000 ))
echo "  Dispatched 20 jobs in ${DURATION}ms"
echo "  Jobs/sec: $(echo "scale=2; 20 / ($DURATION/1000)" | bc)"

echo ""
echo "Load test complete!"
