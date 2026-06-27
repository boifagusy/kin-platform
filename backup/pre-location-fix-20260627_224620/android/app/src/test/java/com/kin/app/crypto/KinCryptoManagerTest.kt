package com.kin.app.crypto

import android.content.Context
import android.content.SharedPreferences
import org.junit.Before
import org.junit.Test
import org.junit.Assert.*
import org.mockito.Mock
import org.mockito.Mockito.*
import org.mockito.MockitoAnnotations

class KinCryptoManagerTest {

    @Mock
    private lateinit var context: Context

    @Mock
    private lateinit var sharedPreferences: SharedPreferences

    @Mock
    private lateinit var editor: SharedPreferences.Editor

    private lateinit var cryptoManager: KinCryptoManager

    @Before
    fun setUp() {
        MockitoAnnotations.openMocks(this)
        `when`(context.getSharedPreferences(anyString(), anyInt())).thenReturn(sharedPreferences)
        `when`(sharedPreferences.edit()).thenReturn(editor)
        `when`(editor.putString(anyString(), anyString())).thenReturn(editor)
        `when`(editor.apply()).then { }

        cryptoManager = KinCryptoManager(context)
    }

    @Test
    fun testEncryptDecrypt() {
        val plaintext = "test_secret_1234"
        val encrypted = cryptoManager.encrypt(plaintext)
        val decrypted = cryptoManager.decrypt(encrypted)

        assertEquals(plaintext, decrypted)
    }

    @Test
    fun testStoreAndRetrieveSecure() {
        val key = "test_key"
        val value = "test_value"

        `when`(sharedPreferences.getString(eq(key), isNull())).thenReturn(
            cryptoManager.encrypt(value)
        )

        cryptoManager.storeSecure(key, value)

        // Verify store was called
        verify(editor).putString(eq(key), anyString())
        verify(editor).apply()

        // Test retrieve
        val retrieved = cryptoManager.retrieveSecure(key)
        assertEquals(value, retrieved)
    }

    @Test
    fun testVerifyDuressPin() {
        val duressPin = "9999"
        val key = "duress_pin"

        `when`(sharedPreferences.getString(eq(key), isNull())).thenReturn(
            cryptoManager.encrypt(duressPin)
        )

        // Store duress pin
        cryptoManager.storeSecure(key, duressPin)

        // Verify correct pin
        val result = cryptoManager.verifyDuressPin(duressPin)
        assertTrue(result)

        // Verify incorrect pin
        val wrongResult = cryptoManager.verifyDuressPin("1111")
        assertFalse(wrongResult)
    }

    @Test
    fun testDeleteSecure() {
        val key = "test_delete"

        cryptoManager.deleteSecure(key)
        verify(editor).remove(key)
        verify(editor).apply()
    }
}
