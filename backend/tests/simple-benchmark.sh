#!/bin/bash

echo "=========================================="
echo "KIN SIMPLE BENCHMARK"
echo "=========================================="
echo ""

# Health endpoint
echo "1. HEALTH ENDPOINT (10 requests)"
total=0
for i in $(seq 1 10); do
    time=$(curl -s -o /dev/null -w "%{time_total}" http://127.0.0.1:8000/api/v1/health)
    total=$(echo "$total + $time" | bc)
    echo "   Request $i: ${time}s"
done
avg=$(echo "scale=3; $total / 10" | bc)
echo "   Average: ${avg}s"
echo ""

# Login endpoint
echo "2. LOGIN ENDPOINT (10 requests)"
total=0
for i in $(seq 1 10); do
    time=$(curl -s -o /dev/null -w "%{time_total}" -X POST http://127.0.0.1:8000/api/v1/auth/login-pin \
      -H "Content-Type: application/json" \
      -d '{"phone":"+2348055586485","pin":"1234"}')
    total=$(echo "$total + $time" | bc)
    echo "   Request $i: ${time}s"
done
avg=$(echo "scale=3; $total / 10" | bc)
echo "   Average: ${avg}s"
echo ""

# Dashboard endpoint
echo "3. DASHBOARD ENDPOINT (10 requests)"
# Get token first
TOKEN=$(curl -s -X POST http://127.0.0.1:8000/api/v1/auth/login-pin \
  -H "Content-Type: application/json" \
  -d '{"phone":"+2348055586485","pin":"1234"}' | grep -o '"token":"[^"]*"' | cut -d'"' -f4)

if [ -n "$TOKEN" ]; then
    total=0
    for i in $(seq 1 10); do
        time=$(curl -s -o /dev/null -w "%{time_total}" -H "Authorization: Bearer $TOKEN" \
          http://127.0.0.1:8000/api/v1/dashboard)
        total=$(echo "$total + $time" | bc)
        echo "   Request $i: ${time}s"
    done
    avg=$(echo "scale=3; $total / 10" | bc)
    echo "   Average: ${avg}s"
else
    echo "   Skipping - Token not available"
fi

echo ""
echo "=========================================="
echo "BENCHMARK COMPLETE"
echo "=========================================="
