def bin_search(arr, target, key=lambda x: x):
    l, r = 0, len(arr) - 1
    while l <= r:
        mid = (l+r)//2
        mid_v = key(arr[mid])
        if mid_v == target:
            return mid
        elif mid_v < target:
            l = mid+1
        else:
            r = mid-1
    return -1

arr = [1,2,4,5,7,9]

print(bin_search(arr,2))
